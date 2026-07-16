<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Section;
use App\Models\StudentEnrollment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the data behind the DepEd School Form 2 (SF2) — Daily Attendance
 * Report of Learners — for one section and one month.
 */
class Sf2ReportService
{
    public function __construct(private readonly SchoolCalendarService $calendar) {}

    /** Single-character SF2 code for a daily status (blank = present). */
    public const CODES = [
        Attendance::STATUS_PRESENT => '',
        Attendance::STATUS_LATE => '/',      // tardy (legend: half-shaded)
        Attendance::STATUS_HALF_DAY => '½',
        Attendance::STATUS_EXCUSED => 'e',   // excused absence
        Attendance::STATUS_ABSENT => 'x',
        Attendance::STATUS_NO_CLASS => '',
    ];

    /** Statuses that count as the learner having attended that day. */
    private const PRESENT = [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY];

    public function build(Section $section, int $year, int $month): array
    {
        $section->loadMissing(['gradeLevel', 'adviser', 'schoolYear', 'school']);

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = (clone $monthStart)->endOfMonth();

        $classDays = $this->calendar->classDays($section->schoolYear, $monthStart, $monthEnd)
            ->map(fn ($d) => Carbon::parse($d))
            ->values();

        $dayColumns = $classDays->map(fn (Carbon $d) => [
            'date' => $d->toDateString(),
            'day' => $d->day,
            'letter' => $this->dayLetter($d),
        ])->all();

        // Every enrolment in the section for the year (any status) — needed for
        // transferred in/out tallies. Active ones make up the roster.
        $allEnrollments = StudentEnrollment::with('student')
            ->where('section_id', $section->id)
            ->get();

        $enrollments = $allEnrollments
            ->filter(fn ($e) => in_array($e->status, [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN], true))
            ->sortBy(fn ($e) => $e->student->last_name.$e->student->first_name)
            ->values();

        // Attendance for the month, keyed by enrollment then date string.
        $attendance = Attendance::where('section_id', $section->id)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->groupBy('student_enrollment_id');

        $males = $this->buildRows($enrollments->where('student.gender', 'Male')->values(), $classDays, $attendance);
        $females = $this->buildRows($enrollments->where('student.gender', 'Female')->values(), $classDays, $attendance);

        $dailyTotals = $this->dailyTotals($classDays, $males, $females);
        $summary = $this->summary($males, $females, $classDays->count(), $dailyTotals, $enrollments, $allEnrollments);

        return [
            'section' => $section,
            'schoolYear' => $section->schoolYear,
            'month' => $monthStart,
            'monthLabel' => $monthStart->format('F Y'),
            'classDays' => $classDays,
            'dayColumns' => $dayColumns,
            'males' => $males,
            'females' => $females,
            'dailyTotals' => $dailyTotals,
            'summary' => $summary,
            'maxColumns' => 25, // SF2 provides 25 daily columns
        ];
    }

    /**
     * @return array<int, array{no:int, name:string, enrollment_id:int, marks:array<string,string>, statuses:array<string,?string>, absent:int, tardy:int}>
     */
    private function buildRows(Collection $enrollments, Collection $classDays, Collection $attendance): array
    {
        $rows = [];
        $no = 1;

        foreach ($enrollments as $enrollment) {
            $records = ($attendance->get($enrollment->id) ?? collect())->keyBy(fn ($a) => $a->attendance_date->toDateString());

            $marks = [];
            $statuses = [];
            $absent = 0;
            $tardy = 0;

            foreach ($classDays as $day) {
                $key = $day->toDateString();
                $status = optional($records->get($key))->status;
                $statuses[$key] = $status;
                $marks[$key] = $status !== null ? (self::CODES[$status] ?? '') : '';

                if ($status === Attendance::STATUS_ABSENT || $status === Attendance::STATUS_EXCUSED) {
                    $absent++;
                }
                if ($status === Attendance::STATUS_LATE) {
                    $tardy++;
                }
            }

            // On SF2 a blank day counts as present, so present = class days − absences.
            $present = max(0, $classDays->count() - $absent);

            $rows[] = [
                'no' => $no++,
                'name' => $enrollment->student->full_name,
                'enrollment_id' => $enrollment->id,
                'marks' => $marks,
                'statuses' => $statuses,
                'absent' => $absent,
                'present' => $present,
                'tardy' => $tardy,
                'consecutiveAbsences' => $this->longestAbsentStreak($statuses),
                'lateEnrolment' => (bool) ($enrollment->is_late_enrollment ?? false),
            ];
        }

        return $rows;
    }

    /** Per-day present counts for male, female and combined (counted from real statuses). */
    private function dailyTotals(Collection $classDays, array $males, array $females): array
    {
        $totals = [];

        foreach ($classDays as $day) {
            $key = $day->toDateString();
            $m = $this->presentCount($males, $key);
            $f = $this->presentCount($females, $key);
            $totals[$key] = ['male' => $m, 'female' => $f, 'combined' => $m + $f];
        }

        return $totals;
    }

    private function presentCount(array $rows, string $dateKey): int
    {
        return collect($rows)->filter(
            fn ($row) => in_array($row['statuses'][$dateKey] ?? null, self::PRESENT, true)
        )->count();
    }

    /**
     * The DepEd SF2 monthly summary table (values split M / F / TOTAL). Where
     * the system does not track a metric (e.g. a beginning-of-year baseline),
     * the end-of-month registration is used, matching common school practice.
     */
    private function summary(array $males, array $females, int $classDaysCount, array $dailyTotals, Collection $active, Collection $all): array
    {
        $mCount = count($males);
        $fCount = count($females);

        $avgM = $classDaysCount > 0 ? round(collect($dailyTotals)->sum('male') / $classDaysCount, 2) : 0.0;
        $avgF = $classDaysCount > 0 ? round(collect($dailyTotals)->sum('female') / $classDaysCount, 2) : 0.0;

        // Transferred in/out for the section this year, by gender.
        $transferredIn = $this->genderCounts($all->where('status', StudentEnrollment::STATUS_TRANSFERRED_IN));
        $transferredOut = $this->genderCounts($all->where('status', StudentEnrollment::STATUS_TRANSFERRED_OUT));
        $lateEnrol = ['male' => $this->countBy($males, 'lateEnrolment'), 'female' => $this->countBy($females, 'lateEnrolment')];
        $absent5 = ['male' => $this->countAtLeast($males, 'consecutiveAbsences', 5), 'female' => $this->countAtLeast($females, 'consecutiveAbsences', 5)];

        // Counts sum M + F; percentages are computed from the TOTAL column, not summed.
        $intTriple = fn (int $m, int $f) => ['male' => $m, 'female' => $f, 'total' => $m + $f];
        $totalCount = $mCount + $fCount;
        $avgTotal = round($avgM + $avgF, 2);

        return [
            'classDays' => $classDaysCount,
            'enrolment' => $intTriple($mCount, $fCount),           // as of 1st Friday (baseline)
            'lateEnrolment' => $intTriple($lateEnrol['male'], $lateEnrol['female']),
            'registered' => $intTriple($mCount, $fCount),          // end of month
            'percentEnrolment' => [                                // registered ÷ enrolment
                'male' => $mCount > 0 ? 1.0 : 0.0,
                'female' => $fCount > 0 ? 1.0 : 0.0,
                'total' => $totalCount > 0 ? 1.0 : 0.0,
            ],
            'avgDaily' => ['male' => $avgM, 'female' => $avgF, 'total' => $avgTotal],
            'percentAttendance' => [                               // ADA ÷ registered
                'male' => $mCount > 0 ? round($avgM / $mCount, 2) : 0.0,
                'female' => $fCount > 0 ? round($avgF / $fCount, 2) : 0.0,
                'total' => $totalCount > 0 ? round($avgTotal / $totalCount, 2) : 0.0,
            ],
            'absent5' => $intTriple($absent5['male'], $absent5['female']),
            'nls' => $intTriple(0, 0),
            'transferredOut' => $intTriple($transferredOut['male'], $transferredOut['female']),
            'transferredIn' => $intTriple($transferredIn['male'], $transferredIn['female']),
        ];
    }

    /** Longest run of consecutive absences across the class days. */
    private function longestAbsentStreak(array $statuses): int
    {
        $longest = $run = 0;
        foreach ($statuses as $status) {
            if ($status === Attendance::STATUS_ABSENT) {
                $longest = max($longest, ++$run);
            } else {
                $run = 0;
            }
        }

        return $longest;
    }

    private function genderCounts(Collection $enrollments): array
    {
        return [
            'male' => $enrollments->filter(fn ($e) => $e->student?->gender === 'Male')->count(),
            'female' => $enrollments->filter(fn ($e) => $e->student?->gender === 'Female')->count(),
        ];
    }

    private function countBy(array $rows, string $flag): int
    {
        return collect($rows)->filter(fn ($r) => $r[$flag] ?? false)->count();
    }

    private function countAtLeast(array $rows, string $key, int $min): int
    {
        return collect($rows)->filter(fn ($r) => ($r[$key] ?? 0) >= $min)->count();
    }

    private function dayLetter(Carbon $date): string
    {
        return match ($date->dayOfWeek) {
            Carbon::MONDAY => 'M',
            Carbon::TUESDAY => 'T',
            Carbon::WEDNESDAY => 'W',
            Carbon::THURSDAY => 'Th',
            Carbon::FRIDAY => 'F',
            Carbon::SATURDAY => 'S',
            default => 'Su',
        };
    }
}
