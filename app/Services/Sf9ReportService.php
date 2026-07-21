<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LearnerValue;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\SubjectAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the data behind the DepEd School Form 9 (SF9) — Learner's Progress
 * Report Card — for one section. Grades and values come from what advisers
 * enter; the attendance record is computed from the same daily attendance and
 * school calendar that drive SF2. Final ratings and averages are derived here,
 * never stored, so the printed card cannot be tampered with.
 */
class Sf9ReportService
{
    /** DepEd passing grade for a learning area and the general average. */
    public const PASSING_GRADE = 75;

    /**
     * The official DepEd descriptors for the K to 12 grading scale, printed as
     * the report card's legend. Ordered high-to-low; each is [floor, label].
     *
     * @var array<int, array{0:int,1:string}>
     */
    public const DESCRIPTORS = [
        [90, 'Outstanding'],
        [85, 'Very Satisfactory'],
        [80, 'Satisfactory'],
        [75, 'Fairly Satisfactory'],
        [0, 'Did Not Meet Expectations'],
    ];

    public function __construct(private readonly SchoolCalendarService $calendar) {}

    /** The descriptor for a final grade, or '' when there is no grade. */
    public function descriptor(?int $grade): string
    {
        if ($grade === null) {
            return '';
        }

        foreach (self::DESCRIPTORS as [$floor, $label]) {
            if ($grade >= $floor) {
                return $label;
            }
        }

        return '';
    }

    /** Grade 11+ is Senior High, which is semestral rather than four straight quarters. */
    public function isSeniorHigh(Section $section): bool
    {
        return (int) ($section->gradeLevel->level_order ?? 0) >= 11;
    }

    /**
     * The education-level tag a section's SF9 carries: ES (elementary, grades
     * 1-6), JHS (grades 7-10), or SHS (grades 11-12). Drives both the printed
     * header label and the semestral-vs-quarterly layout.
     */
    public function levelTag(Section $section): string
    {
        $order = (int) ($section->gradeLevel->level_order ?? 0);

        return match (true) {
            $order >= 11 => 'SHS',
            $order >= 7 => 'JHS',
            default => 'ES',
        };
    }

    /** @return array<int,string> period (1..4) => label */
    public function periodLabels(Section $section): array
    {
        return $this->isSeniorHigh($section)
            ? [1 => '1st Sem · Q1', 2 => '1st Sem · Q2', 3 => '2nd Sem · Q1', 4 => '2nd Sem · Q2']
            : [1 => '1st Quarter', 2 => '2nd Quarter', 3 => '3rd Quarter', 4 => '4th Quarter'];
    }

    /** Active learners of the section, ordered for a class list. */
    public function roster(Section $section): Collection
    {
        return StudentEnrollment::with('student')
            ->where('section_id', $section->id)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->get()
            ->sortBy(fn ($e) => $e->student->last_name.$e->student->first_name)
            ->values();
    }

    /** Subjects (learning areas) offered in this section, ordered by name. */
    public function subjects(Section $section): Collection
    {
        return SubjectAssignment::with('subject')
            ->where('section_id', $section->id)
            ->get()
            ->map(fn ($a) => $a->subject)
            ->filter()
            ->sortBy('name')
            ->values();
    }

    public function build(Section $section): array
    {
        $section->loadMissing(['gradeLevel', 'schoolYear', 'school', 'adviser']);
        $schoolYear = $section->schoolYear;
        $isShs = $this->isSeniorHigh($section);

        $subjects = $this->subjects($section);
        $enrollments = StudentEnrollment::with(['student', 'grades', 'values'])
            ->where('section_id', $section->id)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->get()
            ->sortBy(fn ($e) => $e->student->last_name.$e->student->first_name)
            ->values();

        $months = $this->months($schoolYear);
        $schoolDays = $this->schoolDaysByMonth($schoolYear, $months);

        // All attendance for the section this year, grouped by enrolment then month key.
        $attendance = Attendance::where('section_id', $section->id)
            ->when($schoolYear, fn ($q) => $q->whereBetween('attendance_date', [
                $schoolYear->start_date->toDateString(),
                $schoolYear->end_date->toDateString(),
            ]))
            ->get()
            ->groupBy('student_enrollment_id');

        $learners = $enrollments->map(fn ($e) => $this->buildLearner(
            $e, $subjects, $isShs, $months, $schoolDays, $attendance->get($e->id) ?? collect()
        ))->all();

        return [
            'section' => $section,
            'schoolYear' => $schoolYear,
            'isShs' => $isShs,
            'levelTag' => $this->levelTag($section),
            'periodLabels' => $this->periodLabels($section),
            'subjects' => $subjects,
            'coreValues' => LearnerValue::CORE_VALUES,
            'marks' => LearnerValue::MARKS,
            'descriptors' => self::DESCRIPTORS,
            'passingGrade' => self::PASSING_GRADE,
            'months' => $months,
            'schoolDays' => $schoolDays,
            'totalSchoolDays' => array_sum($schoolDays),
            'learners' => $learners,
        ];
    }

    private function buildLearner(
        StudentEnrollment $enrollment,
        Collection $subjects,
        bool $isShs,
        array $months,
        array $schoolDays,
        Collection $records,
    ): array {
        $gradesBySubject = $enrollment->grades->groupBy('subject_id');

        $subjectRows = $subjects->map(function ($subject) use ($gradesBySubject, $isShs) {
            $byPeriod = ($gradesBySubject->get($subject->id) ?? collect())->keyBy('period');
            $q = [];
            foreach (range(1, 4) as $p) {
                $g = $byPeriod->get($p)?->grade;
                $q[$p] = $g !== null ? (float) $g : null;
            }

            if ($isShs) {
                // A semester's final needs both of its quarters.
                $sem1 = $this->completeAvg([$q[1], $q[2]]);
                $sem2 = $this->completeAvg([$q[3], $q[4]]);

                return ['subject' => $subject->name, 'q' => $q, 'sem1' => $sem1, 'sem2' => $sem2];
            }

            // The final rating is only meaningful once all four quarters exist.
            $final = $this->completeAvg($q);

            return [
                'subject' => $subject->name,
                'q' => $q,
                'final' => $final,
                'remark' => $final === null ? '' : ($final >= self::PASSING_GRADE ? 'Passed' : 'Failed'),
            ];
        })->all();

        // General average(s): mean of subject finals, ignoring subjects with no grades.
        if ($isShs) {
            $gaSem1 = $this->avg(array_map(fn ($r) => $r['sem1'], $subjectRows));
            $gaSem2 = $this->avg(array_map(fn ($r) => $r['sem2'], $subjectRows));
            $generalAverage = ['sem1' => $gaSem1, 'sem2' => $gaSem2];
        } else {
            $ga = $this->avg(array_map(fn ($r) => $r['final'], $subjectRows));
            $generalAverage = ['final' => $ga, 'remark' => $ga === null ? '' : ($ga >= self::PASSING_GRADE ? 'Passed' : 'Failed')];
        }

        // Core values: one mark per behaviour statement, per period. Each core
        // value carries its official DepEd behaviour statements as sub-rows.
        $valuesByCore = $enrollment->values->groupBy('core_value');
        $valueRows = [];
        foreach (LearnerValue::CORE_VALUES as $key => $label) {
            $byBehavior = ($valuesByCore->get($key) ?? collect())->groupBy('behavior');
            $statements = [];
            foreach (LearnerValue::BEHAVIORS[$key] ?? [] as $i => $text) {
                $behavior = $i + 1; // 1-based to match stored index
                $byPeriod = ($byBehavior->get($behavior) ?? collect())->keyBy('period');
                $statements[] = [
                    'behavior' => $behavior,
                    'text' => $text,
                    'marks' => collect(range(1, 4))->mapWithKeys(fn ($p) => [$p => $byPeriod->get($p)?->mark])->all(),
                ];
            }
            $valueRows[$key] = ['label' => $label, 'statements' => $statements];
        }

        return [
            'enrollment_id' => $enrollment->id,
            'student' => $enrollment->student,
            'name' => $enrollment->student->full_name,
            'lrn' => $enrollment->student->lrn,
            'sex' => $enrollment->student->gender,
            'age' => $enrollment->student->ageAsOf($enrollment->schoolYear?->start_date),
            'subjects' => $subjectRows,
            'generalAverage' => $generalAverage,
            'values' => $valueRows,
            'attendance' => $this->attendanceRow($records, $months, $schoolDays),
        ];
    }

    /** Monthly school days / present / absent for one learner. */
    private function attendanceRow(Collection $records, array $months, array $schoolDays): array
    {
        $byDate = $records->keyBy(fn ($a) => $a->attendance_date->toDateString());
        $row = [];
        $totalDays = $totalAbsent = 0;

        foreach ($months as $m) {
            $key = $m['key'];
            $days = $schoolDays[$key] ?? 0;

            $absent = $records->filter(function ($a) use ($m) {
                $d = $a->attendance_date;

                return (int) $d->year === $m['year'] && (int) $d->month === $m['month']
                    && in_array($a->status, [Attendance::STATUS_ABSENT, Attendance::STATUS_EXCUSED], true);
            })->count();

            $present = max(0, $days - $absent);
            $row[$key] = ['days' => $days, 'present' => $present, 'absent' => $absent];
            $totalDays += $days;
            $totalAbsent += $absent;
        }

        $row['total'] = ['days' => $totalDays, 'present' => max(0, $totalDays - $totalAbsent), 'absent' => $totalAbsent];

        return $row;
    }

    /** @return array<int,array{key:string,year:int,month:int,label:string}> */
    private function months($schoolYear): array
    {
        if (! $schoolYear) {
            return [];
        }

        $cursor = $schoolYear->start_date->copy()->startOfMonth();
        $end = $schoolYear->end_date->copy()->startOfMonth();
        $months = [];

        while ($cursor->lte($end)) {
            $months[] = [
                'key' => $cursor->format('Y-m'),
                'year' => (int) $cursor->year,
                'month' => (int) $cursor->month,
                'label' => strtoupper($cursor->format('M')),
            ];
            $cursor->addMonth();
        }

        return $months;
    }

    /** @return array<string,int> month key => class-day count */
    private function schoolDaysByMonth($schoolYear, array $months): array
    {
        $out = [];
        foreach ($months as $m) {
            $start = Carbon::create($m['year'], $m['month'], 1)->startOfDay();
            $end = (clone $start)->endOfMonth();
            $out[$m['key']] = $schoolYear
                ? $this->calendar->classDays($schoolYear, $start, $end)->count()
                : 0;
        }

        return $out;
    }

    /** Rounded mean of the non-null values, or null when there are none. */
    private function avg(array $values): ?int
    {
        $nums = array_values(array_filter($values, fn ($v) => $v !== null));

        return $nums === [] ? null : (int) round(array_sum($nums) / count($nums));
    }

    /**
     * Rounded mean, but only when EVERY value is present — a period-based rating
     * (a subject's final, a semester's grade) is undefined until all its periods
     * are in. Returns null if any value is missing.
     */
    private function completeAvg(array $values): ?int
    {
        if ($values === [] || in_array(null, $values, true)) {
            return null;
        }

        return (int) round(array_sum($values) / count($values));
    }
}
