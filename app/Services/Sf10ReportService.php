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
 *
 * The printed layout mirrors the official SF10-ES scholastic-record page exactly
 * (public/SF10 REPORT FINAL FORM.pdf): the current year's grades fill the first
 * scholastic block, mapped onto the standard elementary learning-area rows.
 */
class Sf10ReportService
{
    /** DepEd passing grade for a learning area and the general average. */
    public const PASSING_GRADE = Sf9ReportService::PASSING_GRADE;

    /**
     * The standard elementary (SF10-ES) learning areas, in the exact order the
     * official form pre-prints them, each with the name variants an adviser's
     * subject might carry so a learner's grades map onto the right row.
     *
     * @var array<int, array{label:string, indent?:bool, aliases:array<int,string>}>
     */
    private const STANDARD_AREAS = [
        ['label' => 'Filipino', 'aliases' => ['filipino']],
        ['label' => 'English', 'aliases' => ['english']],
        ['label' => 'Mathematics', 'aliases' => ['mathematics', 'math', 'mathematic', 'matematika']],
        ['label' => 'Science', 'aliases' => ['science', 'agham']],
        ['label' => 'GMRC (Good Manners and Right Conduct)', 'aliases' => ['gmrc', 'goodmannersandrightconduct', 'esp', 'edukasyonsapagpapakatao', 'valueseducation']],
        ['label' => 'Araling Panlipunan', 'aliases' => ['aralingpanlipunan', 'ap', 'araling']],
        ['label' => 'EPP', 'aliases' => ['epp', 'edukasyongpantahananatpangkabuhayan', 'tle', 'technologyandlivelihoodeducation']],
        ['label' => 'MAPEH', 'aliases' => ['mapeh']],
        ['label' => 'Music & Arts', 'indent' => true, 'aliases' => ['musicandarts', 'musicarts', 'music', 'arts']],
        ['label' => 'Physical Education & Health', 'indent' => true, 'aliases' => ['physicaleducationandhealth', 'physicaleducationhealth', 'pehealth', 'peandhealth', 'physicaleducation', 'peh', 'pe', 'health']],
    ];

    /** The two optional (asterisked) learning areas printed below the blanks. */
    private const ASTERISK_AREAS = [
        ['label' => '*Arabic Language', 'aliases' => ['arabic', 'arabiclanguage', 'arabiclang']],
        ['label' => '*Islamic Values Education', 'aliases' => ['islamic', 'islamicvalueseducation', 'islamicvalues']],
    ];

    /** Blank learning-area rows between the standard areas and the asterisk pair. */
    private const BLANK_ROWS = 3;

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
            'blankRows' => self::BLANK_ROWS,
            'learners' => $learners,
        ];
    }

    private function buildLearner(StudentEnrollment $enrollment, Collection $subjects): array
    {
        $gradesBySubject = $enrollment->grades->groupBy('subject_id');

        // The learner's rating per subject, keyed by the subject's normalised name.
        $bySubject = [];
        $rawRows = [];
        foreach ($subjects as $subject) {
            $byPeriod = ($gradesBySubject->get($subject->id) ?? collect())->keyBy('period');
            $q = [];
            foreach (range(1, 4) as $p) {
                $g = $byPeriod->get($p)?->grade;
                $q[$p] = $g !== null ? (int) round((float) $g) : null;
            }
            $final = $this->completeAvg($q);
            $row = [
                'q' => $q,
                'final' => $final,
                'remark' => $final === null ? '' : ($final >= self::PASSING_GRADE ? 'PASSED' : 'FAILED'),
            ];
            $rawRows[] = $row + ['name' => $subject->name];
            $bySubject[$this->normalize($subject->name)] = $row;
        }

        [$areas, $usedKeys] = $this->mapStandardAreas($bySubject);

        // General average across the section's subject finals (all of them, not
        // just those that mapped onto a standard row).
        $ga = $this->avg(array_map(fn ($r) => $r['final'], $rawRows));

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
            'areas' => $areas,
            'generalAverage' => $ga,
            'generalRemark' => $ga === null ? '' : ($ga >= self::PASSING_GRADE ? 'PASSED' : 'FAILED'),
        ];
    }

    /**
     * Resolve the ordered learning-area rows for the first scholastic block:
     * the standard areas (filled where a subject maps), then blank rows filled
     * with any leftover subjects, then the asterisk pair.
     *
     * @param  array<string, array{q:array,final:?int,remark:string}>  $bySubject
     * @return array{0: array<int, array{label:string, indent:bool, q:array, final:?int, remark:string}>, 1: array<string,bool>}
     */
    private function mapStandardAreas(array $bySubject): array
    {
        $used = [];
        $rows = [];
        $empty = ['q' => [1 => null, 2 => null, 3 => null, 4 => null], 'final' => null, 'remark' => ''];

        $match = function (array $aliases) use ($bySubject, &$used) {
            foreach ($bySubject as $key => $row) {
                if (isset($used[$key])) {
                    continue;
                }
                foreach ($aliases as $alias) {
                    if ($key === $alias) {
                        $used[$key] = true;

                        return $row;
                    }
                }
            }

            return null;
        };

        foreach (self::STANDARD_AREAS as $area) {
            $row = $match($area['aliases']) ?? $empty;
            $rows[] = ['label' => $area['label'], 'indent' => $area['indent'] ?? false] + $row;
        }

        // Any subject that did not map onto a standard row drops into the blank
        // rows so nothing entered is silently lost. The rows stay unlabeled —
        // the ratings still print, matching the template's fillable blank rows.
        $leftover = array_values(array_diff_key($bySubject, $used));
        for ($i = 0; $i < self::BLANK_ROWS; $i++) {
            $row = $leftover[$i] ?? $empty;
            $rows[] = ['label' => '', 'indent' => false] + $row;
        }

        foreach (self::ASTERISK_AREAS as $area) {
            $row = $match($area['aliases']) ?? $empty;
            $rows[] = ['label' => $area['label'], 'indent' => false] + $row;
        }

        return [$rows, $used];
    }

    private function normalize(string $name): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($name)) ?? '';
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
