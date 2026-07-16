<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClassSessionController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    /**
     * Start a class: create (or resume) today's live session for a section,
     * seed the roster as absent, and generate the scanner's QR key.
     */
    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        $teacher = $user->teacher;
        abort_unless($teacher, 403, 'Only teachers can start a class.');

        $data = $request->validate([
            'section_id' => ['required', 'integer'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'teacher_schedule_id' => ['nullable', 'integer', 'exists:teacher_schedules,id'],
        ]);

        // The section must be one the teacher can take attendance for.
        $section = $user->accessibleSections()->whereKey($data['section_id'])->firstOrFail();

        $year = SchoolYear::activeFor($user) ?? abort(422, 'No active school year for your school.');
        abort_unless($section->school_year_id === $year->id, 422,
            'This class belongs to a closed school year — sessions can only run in the active one.');
        $today = Carbon::today();

        // Resume an already-running session rather than spawning duplicates.
        $session = ClassSession::active()
            ->where('teacher_id', $teacher->id)
            ->where('section_id', $section->id)
            ->whereDate('session_date', $today)
            ->first();

        if (! $session) {
            $session = ClassSession::create([
                'school_id' => $section->school_id,
                'teacher_id' => $teacher->id,
                'section_id' => $section->id,
                'subject_id' => $data['subject_id'] ?? null,
                'school_year_id' => $year->id,
                'teacher_schedule_id' => $data['teacher_schedule_id'] ?? null,
                'session_date' => $today->toDateString(),
                'qr_key' => ClassSession::generateKey(),
                'status' => ClassSession::STATUS_ACTIVE,
                'started_at' => Carbon::now(),
            ]);

            $this->attendance->openSession($user, $section, $today);
        }

        return redirect()->route('class-sessions.show', $session);
    }

    /** The teacher's live monitor: QR key + present/absent roster. */
    public function show(Request $request, ClassSession $session)
    {
        $this->authorizeSession($request, $session);

        $session->load(['section.gradeLevel', 'subject']);

        return view('class-sessions.show', $this->board($session));
    }

    public function end(Request $request, ClassSession $session): RedirectResponse
    {
        $this->authorizeSession($request, $session);

        if ($session->isActive()) {
            $session->update([
                'status' => ClassSession::STATUS_ENDED,
                'ended_at' => Carbon::now(),
            ]);
        }

        return redirect()->route('teacher.dashboard')->with('success', 'Class ended. Attendance saved.');
    }

    /** Build the roster board (learners + live present/absent status). */
    private function board(ClassSession $session): array
    {
        $roster = $this->attendance->roster($session->section);

        $marks = Attendance::where('section_id', $session->section_id)
            ->whereDate('attendance_date', $session->session_date)
            ->get()
            ->keyBy('student_enrollment_id');

        $present = $marks->whereIn('status', Attendance::presentStatuses())->count();

        return [
            'session' => $session,
            'roster' => $roster,
            'marks' => $marks,
            'present' => $present,
            'total' => $roster->count(),
        ];
    }

    private function authorizeSession(Request $request, ClassSession $session): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $session->teacher?->user_id === $user->id,
            403
        );
    }
}
