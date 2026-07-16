<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\ClassSessionAttendance;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Finds learners who were in school but skipped a period.
 *
 * A learner "cut" a class when, on the same day, they scanned into at least one
 * of their section's sessions but not into another — being seen in one period
 * proves they were on campus, so a missed period is a skip rather than a plain
 * day-long absence.
 *
 * Sessions are matched by section, so a class run by a different teacher still
 * counts: an adviser sees every period their advisory learners skipped.
 */
class CuttingClassService
{
    /**
     * @return Collection<int, array{
     *     student: \App\Models\Student,
     *     section: Section,
     *     attended: Collection<int, ClassSession>,
     *     skipped: Collection<int, ClassSession>
     * }>
     */
    public function forAdviser(User $user, ?Carbon $date = null): Collection
    {
        $date ??= Carbon::today();
        $activeYear = SchoolYear::activeFor($user);

        if (! $activeYear) {
            return collect();
        }

        $sections = $user->advisorySections()
            ->where('school_year_id', $activeYear->id)
            ->pluck('id');

        if ($sections->isEmpty()) {
            return collect();
        }

        // Every period that actually ran today for those sections, any teacher.
        $sessions = ClassSession::withoutGlobalScopes()
            ->with(['subject', 'teacher', 'teacherSchedule'])
            ->whereIn('section_id', $sections)
            ->whereDate('session_date', $date)
            ->orderBy('started_at')
            ->get()
            ->groupBy('section_id');

        if ($sessions->isEmpty()) {
            return collect();
        }

        $scanned = ClassSessionAttendance::query()
            ->whereIn('class_session_id', $sessions->flatten()->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $enrollments = StudentEnrollment::with(['student', 'section.gradeLevel'])
            ->whereIn('section_id', $sections)
            ->where('school_year_id', $activeYear->id)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->get();

        return $enrollments->map(function (StudentEnrollment $enrollment) use ($sessions, $scanned) {
            $todays = $sessions->get($enrollment->section_id) ?? collect();
            $seen = ($scanned->get($enrollment->student_id) ?? collect())->pluck('class_session_id')->flip();

            $attended = $todays->filter(fn ($s) => $seen->has($s->id))->values();
            $skipped = $todays->reject(fn ($s) => $seen->has($s->id))->values();

            return [
                'student' => $enrollment->student,
                'section' => $enrollment->section,
                'attended' => $attended,
                'skipped' => $skipped,
            ];
        })
            // Seen in at least one period but missed another — never a full-day absence.
            ->filter(fn ($row) => $row['attended']->isNotEmpty() && $row['skipped']->isNotEmpty())
            ->sortByDesc(fn ($row) => $row['skipped']->count())
            ->values();
    }

    /** How many advisory learners cut at least one period on the given day. */
    public function countForAdviser(User $user, ?Carbon $date = null): int
    {
        return $this->forAdviser($user, $date)->count();
    }
}
