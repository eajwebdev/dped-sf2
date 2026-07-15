<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\AuditLogger;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollment,
        private readonly AuditLogger $audit,
    ) {}

    /** Section roster + a panel of learners available to enrol. */
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $activeYear = SchoolYear::current();
        $yearId = $request->integer('school_year_id') ?: $activeYear?->id;

        $sections = Section::with('gradeLevel')
            ->where('school_year_id', $yearId)
            ->orderBy('grade_level_id')->orderBy('name')
            ->get();

        $sectionId = $request->integer('section_id') ?: $sections->first()?->id;
        $section = $sectionId ? $sections->firstWhere('id', $sectionId) : null;

        $roster = collect();
        $available = collect();

        if ($section) {
            $roster = StudentEnrollment::with('student')
                ->where('section_id', $section->id)
                ->orderByDesc('status')
                ->get()
                ->sortBy(fn ($e) => $e->student->last_name)
                ->values();

            // Learners with no enrollment yet in this school year.
            $enrolledStudentIds = StudentEnrollment::where('school_year_id', $yearId)->pluck('student_id');
            $search = trim((string) $request->get('q'));
            $available = Student::query()
                ->whereNotIn('id', $enrolledStudentIds)
                ->where('status', 'active')
                ->when($search, fn ($q) => $q->where(fn ($w) => $w
                    ->where('lrn', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")))
                ->orderBy('last_name')->limit(50)->get();
        }

        return view('admin.enrollments.index', [
            'schoolYears' => SchoolYear::orderByDesc('start_date')->get(),
            'sections' => $sections,
            'section' => $section,
            'selectedYearId' => $yearId,
            'roster' => $roster,
            'available' => $available,
            'search' => $request->get('q'),
        ]);
    }

    /** Bulk-enroll selected learners into a section. */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
            'is_late_enrollment' => ['boolean'],
        ]);

        $section = Section::findOrFail($data['section_id']);
        $count = 0;
        $skipped = [];

        foreach (Student::whereIn('id', $data['student_ids'])->get() as $student) {
            try {
                $this->enrollment->enroll($student, $section, [
                    'is_late_enrollment' => $request->boolean('is_late_enrollment'),
                ]);
                $count++;
            } catch (ValidationException $e) {
                $skipped[] = $student->full_name;
            }
        }

        $msg = "{$count} learner(s) enrolled into {$section->name}.";
        if ($skipped) {
            $msg .= ' Skipped (already enrolled this year): '.implode(', ', $skipped).'.';
        }

        return redirect()
            ->route('admin.enrollments.index', ['school_year_id' => $section->school_year_id, 'section_id' => $section->id])
            ->with('success', $msg);
    }

    public function transfer(Request $request, StudentEnrollment $enrollment): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        $this->enrollment->transfer($enrollment, Section::findOrFail($data['section_id']));

        return back()->with('success', 'Learner transferred.');
    }

    public function changeStatus(Request $request, StudentEnrollment $enrollment): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                StudentEnrollment::STATUS_ENROLLED,
                StudentEnrollment::STATUS_TRANSFERRED_OUT,
                StudentEnrollment::STATUS_DROPPED,
            ])],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $this->enrollment->changeStatus($enrollment, $data['status'], $data['remarks'] ?? null);

        return back()->with('success', 'Enrollment status updated.');
    }

    public function destroy(Request $request, StudentEnrollment $enrollment): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($enrollment->attendance()->exists()) {
            return back()->with('error', 'Cannot remove: this enrollment already has attendance records. Set status to Dropped/Transferred instead.');
        }

        $section = $enrollment->section;
        $this->audit->deleted($enrollment, 'Enrollment removed');
        $enrollment->delete();

        return redirect()
            ->route('admin.enrollments.index', ['school_year_id' => $section->school_year_id, 'section_id' => $section->id])
            ->with('success', 'Enrollment removed.');
    }
}
