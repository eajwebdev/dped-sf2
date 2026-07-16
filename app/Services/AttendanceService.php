<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\AttendanceUnlock;
use App\Models\ClassSession;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public const VALID_STATUSES = [
        Attendance::STATUS_PRESENT,
        Attendance::STATUS_ABSENT,
        Attendance::STATUS_LATE,
        Attendance::STATUS_EXCUSED,
        Attendance::STATUS_HALF_DAY,
        Attendance::STATUS_NO_CLASS,
    ];

    public function __construct(private readonly SchoolCalendarService $calendar) {}

    public function settingsFor(Section $section): AttendanceSetting
    {
        return AttendanceSetting::resolve($section->school_year_id);
    }

    /** Active enrolments for a section (the learners to mark), ordered for display. */
    public function roster(Section $section)
    {
        return StudentEnrollment::with('student')
            ->where('section_id', $section->id)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->get()
            ->sortBy(fn ($e) => $e->student->last_name.$e->student->first_name)
            ->values();
    }

    public function isClassDay(Section $section, Carbon $date): bool
    {
        return $this->calendar->isClassDay($section->schoolYear, $date);
    }

    public function isUnlocked(Section $section, Carbon $date): bool
    {
        return AttendanceUnlock::where('section_id', $section->id)
            ->whereDate('date', $date)->exists();
    }

    /** Beyond the editable window (older than edit_lock_days). */
    public function windowLocked(Carbon $date, AttendanceSetting $settings): bool
    {
        if ($settings->edit_lock_days <= 0) {
            return false;
        }

        return $date->lt(Carbon::today()->subDays($settings->edit_lock_days));
    }

    /**
     * Whether the given user may edit attendance for this section on this date.
     *
     * @return array{editable: bool, reason: ?string}
     */
    public function editability(User $user, Section $section, Carbon $date, ?AttendanceSetting $settings = null): array
    {
        $settings ??= $this->settingsFor($section);
        $isAdmin = $user->isAdmin();

        if ($date->gt(Carbon::today()) && ($settings->block_future_dates && ! $isAdmin)) {
            return ['editable' => false, 'reason' => 'future'];
        }

        if (! $this->isClassDay($section, $date) && ! $settings->allow_holiday_override && ! $isAdmin) {
            return ['editable' => false, 'reason' => 'holiday'];
        }

        if ($this->windowLocked($date, $settings) && ! $this->isUnlocked($section, $date) && ! $isAdmin) {
            return ['editable' => false, 'reason' => 'locked'];
        }

        return ['editable' => true, 'reason' => null];
    }

    /** Build everything the marking grid needs for a section + date. */
    public function sheet(User $user, Section $section, Carbon $date): array
    {
        $settings = $this->settingsFor($section);
        $roster = $this->roster($section);

        $existing = Attendance::where('section_id', $section->id)
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('student_enrollment_id');

        $edit = $this->editability($user, $section, $date, $settings);

        return [
            'section' => $section,
            'date' => $date,
            'roster' => $roster,
            'existing' => $existing,
            'settings' => $settings,
            'isClassDay' => $this->isClassDay($section, $date),
            'editable' => $edit['editable'],
            'lockReason' => $edit['reason'],
            'summary' => $this->summarize($existing, $roster->count()),
        ];
    }

    /**
     * Persist a batch of marks. Each mark: ['enrollment_id' => int, 'status' => string, 'remarks' => ?string].
     * An empty/omitted status clears the record. Returns a save summary.
     *
     * @param  array<int, array{enrollment_id:int, status:?string, remarks:?string}>  $marks
     * @return array{saved:int, errors:array<string>}
     */
    public function save(User $user, Section $section, Carbon $date, array $marks): array
    {
        $edit = $this->editability($user, $section, $date);
        if (! $edit['editable']) {
            return ['saved' => 0, 'errors' => [$this->lockMessage($edit['reason'])]];
        }

        // Restrict to enrolments that actually belong to this section.
        $enrollments = StudentEnrollment::where('section_id', $section->id)
            ->whereKey(collect($marks)->pluck('enrollment_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $existing = Attendance::where('section_id', $section->id)
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('student_enrollment_id');

        $saved = 0;
        $errors = [];

        DB::transaction(function () use ($marks, $enrollments, $existing, $section, $date, $user, &$saved, &$errors) {
            foreach ($marks as $mark) {
                $enrollment = $enrollments->get($mark['enrollment_id'] ?? null);
                if (! $enrollment) {
                    continue;
                }

                $status = $mark['status'] ?? null;
                $remarks = $mark['remarks'] ?? null;
                $current = $existing->get($enrollment->id);

                // Clear an existing mark when status is blanked out.
                if ($status === null || $status === '') {
                    if ($current) {
                        $this->logChange($current, $user, 'deleted', $current->status, null, $current->remarks, null);
                        $current->delete();
                        $saved++;
                    }

                    continue;
                }

                if (! in_array($status, self::VALID_STATUSES, true)) {
                    $errors[] = "Invalid status '{$status}'.";

                    continue;
                }

                if ($current) {
                    if ($current->status !== $status || $current->remarks !== $remarks) {
                        $old = [$current->status, $current->remarks];
                        $current->update(['status' => $status, 'remarks' => $remarks, 'marked_by' => $user->id]);
                        $this->logChange($current, $user, 'updated', $old[0], $status, $old[1], $remarks);
                        $saved++;
                    }
                } else {
                    $record = Attendance::create([
                        'student_enrollment_id' => $enrollment->id,
                        'student_id' => $enrollment->student_id,
                        'section_id' => $section->id,
                        'school_year_id' => $section->school_year_id,
                        'attendance_date' => $date->toDateString(),
                        'status' => $status,
                        'remarks' => $remarks,
                        'marked_by' => $user->id,
                    ]);
                    $this->logChange($record, $user, 'created', null, $status, null, $remarks);
                    $saved++;
                }
            }
        });

        return ['saved' => $saved, 'errors' => $errors];
    }

    /**
     * Start-of-class seeding: mark every active learner in the section ABSENT
     * for the day (only where no record exists yet). Scanning later flips them
     * to present. Returns the number of learners seeded.
     */
    public function openSession(User $user, Section $section, Carbon $date): int
    {
        $roster = $this->roster($section);

        $already = Attendance::where('section_id', $section->id)
            ->whereDate('attendance_date', $date)
            ->pluck('student_enrollment_id')
            ->all();

        $created = 0;

        DB::transaction(function () use ($roster, $already, $section, $date, $user, &$created) {
            foreach ($roster as $enrollment) {
                if (in_array($enrollment->id, $already, true)) {
                    continue;
                }

                $record = Attendance::create([
                    'school_id' => $section->school_id,
                    'student_enrollment_id' => $enrollment->id,
                    'student_id' => $enrollment->student_id,
                    'section_id' => $section->id,
                    'school_year_id' => $section->school_year_id,
                    'attendance_date' => $date->toDateString(),
                    'status' => Attendance::STATUS_ABSENT,
                    'marked_by' => $user->id,
                ]);
                $this->logChange($record, $user, 'created', null, Attendance::STATUS_ABSENT, null, 'Class started');
                $created++;
            }
        });

        return $created;
    }

    /**
     * Flip a scanned learner to present within a live class session. Runs
     * without an authenticated user (the session qr_key is the credential), so
     * it bypasses the editable-window gate and pins everything to the session.
     *
     * @return array{ok: bool, name?: string, status?: string, message?: string}
     */
    public function markPresentForSession(ClassSession $session, Student $student): array
    {
        $enrollment = StudentEnrollment::withoutGlobalScopes()
            ->where('section_id', $session->section_id)
            ->where('student_id', $student->id)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->first();

        if (! $enrollment) {
            return ['ok' => false, 'message' => "{$student->full_name} is not in this class."];
        }

        $record = Attendance::withoutGlobalScopes()
            ->where('student_enrollment_id', $enrollment->id)
            ->whereDate('attendance_date', $session->session_date)
            ->first();

        $values = [
            'status' => Attendance::STATUS_PRESENT,
            'time_in' => Carbon::now()->format('H:i:s'),
            'marked_by' => $session->teacher?->user_id,
        ];

        if ($record) {
            $record->update($values);
        } else {
            Attendance::create($values + [
                'school_id' => $session->school_id,
                'student_enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'section_id' => $session->section_id,
                'school_year_id' => $session->school_year_id,
                'attendance_date' => $session->session_date->toDateString(),
            ]);
        }

        return ['ok' => true, 'name' => $student->full_name, 'status' => Attendance::STATUS_PRESENT];
    }

    /** Admin: re-open a locked date so a teacher can amend it. */
    public function unlock(User $admin, Section $section, Carbon $date): void
    {
        AttendanceUnlock::firstOrCreate(
            ['section_id' => $section->id, 'date' => $date->toDateString()],
            ['unlocked_by' => $admin->id],
        );
    }

    private function logChange(Attendance $attendance, User $user, string $action, ?string $oldStatus, ?string $newStatus, ?string $oldRemarks, ?string $newRemarks): void
    {
        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'old_remarks' => $oldRemarks,
            'new_remarks' => $newRemarks,
            'ip_address' => request()->ip(),
        ]);
    }

    private function summarize($existing, int $total): array
    {
        $counts = $existing->groupBy('status')->map->count();

        return [
            'present' => $counts->get('present', 0),
            'absent' => $counts->get('absent', 0),
            'late' => $counts->get('late', 0),
            'excused' => $counts->get('excused', 0),
            'half_day' => $counts->get('half_day', 0),
            'unmarked' => max(0, $total - $existing->count()),
            'total' => $total,
        ];
    }

    private function lockMessage(?string $reason): string
    {
        return match ($reason) {
            'future' => 'You cannot record attendance for a future date.',
            'holiday' => 'This is not a class day. Ask an administrator to override the calendar.',
            'locked' => 'This date is locked for editing. Ask an administrator to unlock it.',
            default => 'Attendance for this date cannot be edited.',
        };
    }
}
