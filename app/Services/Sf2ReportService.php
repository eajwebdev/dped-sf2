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
        $section->loadMissing(['gradeLevel', 'adviser', 'schoolYear']);

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

        // Active learners in the section, split by gender, alphabetised.
        $enrollments = StudentEnrollment::with('student')
            ->where('section_id', $section->id)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->get()
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
        $summary = $this->summary($males, $females, $classDays->count(), $dailyTotals);

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

            $rows[] = [
                'no' => $no++,
                'name' => $enrollment->student->full_name,
                'enrollment_id' => $enrollment->id,
                'marks' => $marks,
                'statuses' => $statuses,
                'absent' => $absent,
                'tardy' => $tardy,
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

    private function summary(array $males, array $females, int $classDaysCount, array $dailyTotals): array
    {
        $mCount = count($males);
        $fCount = count($females);

        $mDailySum = collect($dailyTotals)->sum('male');
        $fDailySum = collect($dailyTotals)->sum('female');

        $avgM = $classDaysCount > 0 ? round($mDailySum / $classDaysCount, 2) : 0;
        $avgF = $classDaysCount > 0 ? round($fDailySum / $classDaysCount, 2) : 0;

        return [
            'enrolment' => ['male' => $mCount, 'female' => $fCount, 'total' => $mCount + $fCount],
            'classDays' => $classDaysCount,
            'avgDaily' => ['male' => $avgM, 'female' => $avgF, 'total' => round($avgM + $avgF, 2)],
            'percentAttendance' => [
                'male' => $mCount > 0 ? round($avgM / $mCount * 100, 1) : 0,
                'female' => $fCount > 0 ? round($avgF / $fCount * 100, 1) : 0,
                'total' => ($mCount + $fCount) > 0 ? round(($avgM + $avgF) / ($mCount + $fCount) * 100, 1) : 0,
            ],
        ];
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
