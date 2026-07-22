<?php

namespace App\Services;

use App\Models\Section;
use App\Models\StudentEnrollment;
use Illuminate\Support\Collection;

/**
 * Builds the data behind DepEd School Form 10 (SF10-ES) — the Learner Permanent
 * Academic Record, formerly Form 137 — for one section. The scholastic record
 * reuses the same quarterly ratings advisers enter for SF9: each learning area
 * carries four quarterly ratings, a final rating (the mean of the four, only
 * once all are in), a Passed/Failed remark, and a general average. Nothing is
 * stored — the finals and averages are derived here — so the printed permanent
 * record cannot be tampered with. See [[roles-and-tenancy]].
 */
class Sf10ReportService
{
    /** DepEd passing grade for a learning area and the general average. */
    public const PASSING_GRADE = Sf9ReportService::PASSING_GRADE;

    public function __construct(private readonly Sf9ReportService $sf9) {}

    public function build(Section $section): array
    {
        $section->loadMissing(['gradeLevel', 'schoolYear', 'school', 'adviser']);

        $subjects = $this->sf9->subjects($section);

        $enrollments = StudentEnrollment::with(['student', 'grades'])
            ->where('section_id', $section->id)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->get()
            ->sortBy(fn ($e) => $e->student->last_name.$e->student->first_name)
            ->values();

        $learners = $enrollments->map(fn ($e) => $this->buildLearner($e, $subjects))->all();

        return [
            'section' => $section,
            'schoolYear' => $section->schoolYear,
            'school' => $section->school,
            'subjects' => $subjects,
            'passingGrade' => self::PASSING_GRADE,
            'learners' => $learners,
        ];
    }

    private function buildLearner(StudentEnrollment $enrollment, Collection $subjects): array
    {
        $gradesBySubject = $enrollment->grades->groupBy('subject_id');

        $subjectRows = $subjects->map(function ($subject) use ($gradesBySubject) {
            $byPeriod = ($gradesBySubject->get($subject->id) ?? collect())->keyBy('period');
            $q = [];
            foreach (range(1, 4) as $p) {
                $g = $byPeriod->get($p)?->grade;
                $q[$p] = $g !== null ? (int) round((float) $g) : null;
            }

            // The final rating is only meaningful once all four quarters exist.
            $final = $this->completeAvg($q);

            return [
                'subject' => $subject->name,
                'q' => $q,
                'final' => $final,
                'remark' => $final === null ? '' : ($final >= self::PASSING_GRADE ? 'PASSED' : 'FAILED'),
            ];
        })->all();

        // General average: mean of subject finals, ignoring subjects with no
        // final yet. Blank until at least one learning area is complete.
        $ga = $this->avg(array_map(fn ($r) => $r['final'], $subjectRows));

        $student = $enrollment->student;

        return [
            'enrollment_id' => $enrollment->id,
            'student' => $student,
            'lastName' => $student->last_name,
            'firstName' => $student->first_name,
            'middleName' => $student->middle_name,
            'suffix' => $student->suffix,
            'lrn' => $student->lrn,
            'sex' => $student->gender,
            'birthdate' => $student->birthdate,
            'subjects' => $subjectRows,
            'generalAverage' => $ga,
            'generalRemark' => $ga === null ? '' : ($ga >= self::PASSING_GRADE ? 'PASSED' : 'FAILED'),
        ];
    }

    /** Rounded mean of the non-null values, or null when there are none. */
    private function avg(array $values): ?int
    {
        $nums = array_values(array_filter($values, fn ($v) => $v !== null));

        return $nums === [] ? null : (int) round(array_sum($nums) / count($nums));
    }

    /**
     * Rounded mean, but only when EVERY value is present — a subject's final
     * rating is undefined until all four quarters are in. Null if any is missing.
     */
    private function completeAvg(array $values): ?int
    {
        if ($values === [] || in_array(null, $values, true)) {
            return null;
        }

        return (int) round(array_sum($values) / count($values));
    }
}
