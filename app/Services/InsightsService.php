<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\TextbookIssuance;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * The Professional plan's "Advanced reports": one adviser's classes, analysed
 * from data the School Forms already collect. Everything here is derived on
 * read — there is nothing to keep in sync.
 *
 * The Enterprise tier's school-wide analytics is the same idea rolled up
 * across every section; this service stays deliberately adviser-scoped.
 */
class InsightsService
{
    /** Streak length at which a learner appears on the watchlist. */
    public const WATCHLIST_STREAK = 3;

    /** The SF2 intervention threshold (5 consecutive absences). */
    public const CRITICAL_STREAK = 5;

    /** Attendance below this share of recorded days flags a learner. */
    public const LOW_ATTENDANCE_PERCENT = 85;

    /**
     * The low-attendance flag needs this many recorded days before it fires —
     * one bad day out of two is not a pattern. Streaks are exempt: three
     * straight absences mean the same thing however new the record is.
     */
    public const MIN_DAYS_FOR_RATE_FLAG = 10;

    /** @return array<string, mixed> */
    public function build(User $user, Section $section): array
    {
        $section->loadMissing(['gradeLevel', 'schoolYear']);
        $schoolYear = $section->schoolYear;

        $enrollments = StudentEnrollment::with('student')
            ->where('section_id', $section->id)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->get();

        $records = Attendance::where('section_id', $section->id)
            ->orderBy('attendance_date')
            ->get()
            ->groupBy('student_enrollment_id');

        /*
         * All rates divide by days the class actually recorded, never by the
         * calendar. A class that adopted the app last week has one week of
         * truth — measuring it against 285 calendar days would flag everyone.
         */
        $markedDays = Attendance::where('section_id', $section->id)
            ->where('status', '!=', Attendance::STATUS_NO_CLASS)
            ->distinct('attendance_date')
            ->count('attendance_date');

        [$learners, $watchlist] = $this->learnerStats($enrollments, $records);

        return [
            'section' => $section,
            'schoolYear' => $schoolYear,
            'tiles' => $this->tiles($enrollments, $learners, $markedDays),
            'monthlyTrend' => $this->monthlyTrend($section, $enrollments->count()),
            'watchlist' => $watchlist,
            'tardiest' => collect($learners)->filter(fn ($l) => $l['tardies'] > 0)
                ->sortByDesc('tardies')->take(5)->values()->all(),
            'books' => $this->bookStats($section),
            'bands' => $this->bandDistribution($enrollments),
        ];
    }

    /**
     * Per-learner attendance stats, and the watchlist of learners who need
     * attention now (absence streaks or low attendance share).
     *
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function learnerStats($enrollments, $records): array
    {
        $learners = [];
        $watchlist = [];

        foreach ($enrollments as $enrollment) {
            $rows = ($records->get($enrollment->id) ?? collect())
                ->where('status', '!=', Attendance::STATUS_NO_CLASS);

            $present = $rows->whereIn('status', [
                Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY,
            ])->count();
            $absences = $rows->whereIn('status', [
                Attendance::STATUS_ABSENT, Attendance::STATUS_EXCUSED,
            ])->count();
            $tardies = $rows->where('status', Attendance::STATUS_LATE)->count();

            // Current open streak: consecutive absences counting back from the
            // most recent record — "is slipping now", not "slipped in June".
            $currentStreak = 0;
            foreach ($rows->reverse() as $row) {
                if ($row->status === Attendance::STATUS_ABSENT) {
                    $currentStreak++;
                } elseif ($row->status !== Attendance::STATUS_NO_CLASS) {
                    break;
                }
            }

            $recordedDays = $rows->count();
            $rate = $recordedDays > 0 ? (int) round(100 * $present / $recordedDays) : 100;

            $learner = [
                'name' => $enrollment->student->last_name.', '.$enrollment->student->first_name,
                'gender' => $enrollment->student->gender,
                'present' => $present,
                'absences' => $absences,
                'tardies' => $tardies,
                'streak' => $currentStreak,
                'rate' => min(100, $rate),
                'recordedDays' => $recordedDays,
            ];
            $learners[] = $learner;

            if ($currentStreak >= self::WATCHLIST_STREAK) {
                $watchlist[] = $learner + [
                    'reason' => 'streak',
                    'critical' => $currentStreak >= self::CRITICAL_STREAK,
                ];
            } elseif ($recordedDays >= self::MIN_DAYS_FOR_RATE_FLAG
                && $learner['rate'] < self::LOW_ATTENDANCE_PERCENT) {
                $watchlist[] = $learner + ['reason' => 'rate', 'critical' => false];
            }
        }

        usort($watchlist, fn ($a, $b) => [$b['critical'], $b['streak'], $a['rate']]
            <=> [$a['critical'], $a['streak'], $b['rate']]);

        return [$learners, $watchlist];
    }

    private function tiles($enrollments, array $learners, int $markedDays): array
    {
        // Learners with no recorded days sit at a neutral 100 — exclude them
        // from the average rather than let them inflate it.
        $rated = collect($learners)->filter(fn ($l) => $l['recordedDays'] > 0);

        return [
            'learners' => $enrollments->count(),
            'daysRecorded' => $markedDays,
            'avgRate' => (int) round($rated->avg('rate') ?? 0),
            'totalAbsences' => collect($learners)->sum('absences'),
            'totalTardies' => collect($learners)->sum('tardies'),
            'perfect' => $rated->filter(fn ($l) => $l['absences'] === 0 && $l['tardies'] === 0)->count(),
        ];
    }

    /**
     * Attendance share per month, computed only over days that month actually
     * has records for — months with no marks are simply not shown.
     *
     * @return array<int, array{label:string, rate:int, days:int}>
     */
    private function monthlyTrend(Section $section, int $enrolled): array
    {
        $enrolled = max(1, $enrolled);

        $rows = Attendance::where('section_id', $section->id)
            ->where('status', '!=', Attendance::STATUS_NO_CLASS)
            ->orderBy('attendance_date')
            ->get(['attendance_date', 'status'])
            ->groupBy(fn ($r) => $r->attendance_date->format('Y-m'));

        $trend = [];
        foreach ($rows as $month => $records) {
            $days = $records->unique(fn ($r) => $r->attendance_date->toDateString())->count();
            $attended = $records->whereIn('status', [
                Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY,
            ])->count();

            $trend[] = [
                'label' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'rate' => min(100, (int) round(100 * $attended / ($days * $enrolled))),
                'days' => $days,
            ];
        }

        return $trend;
    }

    /** Outstanding and lost copies across the section's textbooks. */
    private function bookStats(Section $section): array
    {
        $issuances = TextbookIssuance::whereHas('textbook', fn ($q) => $q->where('section_id', $section->id))
            ->whereNotNull('issued_at')
            ->get();

        return [
            'issued' => $issuances->count(),
            'returned' => $issuances->whereNotNull('returned_at')->count(),
            'lost' => $issuances->whereNull('returned_at')->whereNotNull('return_code')->count(),
            'outstanding' => $issuances->whereNull('returned_at')->whereNull('return_code')->count(),
        ];
    }

    /**
     * Learners per SF5 proficiency band, for the grade-distribution panel.
     *
     * @return array<string, int>
     */
    private function bandDistribution($enrollments): array
    {
        $sf5 = app(Sf5ReportService::class);
        $bands = array_fill_keys(array_keys(Sf5ReportService::BANDS), 0);

        foreach ($enrollments as $enrollment) {
            if ($enrollment->general_average !== null) {
                $bands[$sf5->band((float) $enrollment->general_average)]++;
            }
        }

        return $bands;
    }
}
