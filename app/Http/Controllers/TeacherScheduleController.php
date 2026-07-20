<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\TeacherSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TeacherScheduleController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $this->teacherOrAbort($request);
        $activeYear = SchoolYear::activeFor($request->user());

        $schedules = TeacherSchedule::with(['section.gradeLevel', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->when($activeYear, fn ($q) => $q->where('school_year_id', $activeYear->id))
            ->orderBy('day_of_week')->orderBy('start_time')
            ->get();

        // Every section in the teacher's school for the active year — a teacher
        // may schedule any class, including ones they do not advise, which is
        // what lets them generate a non-advisory SF2 for it later. The school
        // tenant scope keeps this to their own school.
        $sections = Section::query()
            ->with(['gradeLevel', 'subjectAssignments.subject'])
            ->when($activeYear, fn ($q) => $q->where('school_year_id', $activeYear->id))
            ->orderBy('grade_level_id')->orderBy('name')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'label' => "{$s->gradeLevel->name} — {$s->name}",
                'is_advisory' => $s->adviser_id === $teacher->id,
                'subjects' => $s->subjectAssignments
                    ->filter(fn ($sa) => $sa->subject)
                    ->map(fn ($sa) => ['id' => $sa->subject->id, 'name' => $sa->subject->name])
                    ->values(),
            ]);

        return view('teacher.schedule', [
            'schedules' => $schedules,
            'sections' => $sections,
            'activeYear' => $activeYear,
            'teacher' => $teacher,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->teacherOrAbort($request);
        $data = $this->validated($request);

        $this->guardOverlap($teacher->id, $data);

        TeacherSchedule::create($data + [
            'teacher_id' => $teacher->id,
            'school_year_id' => SchoolYear::active()->firstOrFail()->id,
        ]);

        return back()->with('success', 'Schedule added.');
    }

    public function update(Request $request, TeacherSchedule $schedule): RedirectResponse
    {
        $teacher = $this->teacherOrAbort($request);
        abort_unless($schedule->teacher_id === $teacher->id, 403);

        $data = $this->validated($request);
        $this->guardOverlap($teacher->id, $data, $schedule->id);

        $schedule->update($data);

        return back()->with('success', 'Schedule updated.');
    }

    public function destroy(Request $request, TeacherSchedule $schedule): RedirectResponse
    {
        $teacher = $this->teacherOrAbort($request);
        abort_unless($schedule->teacher_id === $teacher->id, 403);

        $schedule->delete();

        return back()->with('success', 'Schedule removed.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        // A teacher may schedule any section in their own school (the tenant
        // scope on Section enforces that); cross-school ids are rejected.
        $activeYear = SchoolYear::activeFor($request->user());
        $sectionIds = Section::query()
            ->when($activeYear, fn ($q) => $q->where('school_year_id', $activeYear->id))
            ->pluck('id');

        return $request->validate([
            'section_id' => ['required', 'integer', 'in:'.$sectionIds->implode(',')],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'in:'.implode(',', TeacherSchedule::COLORS)],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function guardOverlap(int $teacherId, array $data, ?int $ignoreId = null): void
    {
        $yearId = SchoolYear::active()->firstOrFail()->id;

        if (TeacherSchedule::overlaps($teacherId, $yearId, $data['day_of_week'], $data['start_time'], $data['end_time'], $ignoreId)) {
            throw ValidationException::withMessages([
                'start_time' => 'This time overlaps another class on your schedule for that day.',
            ]);
        }
    }

    private function teacherOrAbort(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403, 'Your account is not linked to a teacher record.');

        return $teacher;
    }
}
