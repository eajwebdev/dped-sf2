<?php

namespace App\Services;

use App\Models\Section;
use App\Models\StudentEnrollment;
use Illuminate\Support\Collection;

/**
 * Builds the DepEd School Form 5 (SF5) — Report on Promotion & Level of
 * Proficiency — for one section: every learner with LRN, general average
 * (numeric + descriptive letter), the action taken, and incomplete subjects,
 * plus the promotion and proficiency summary tables.
 */
class Sf5ReportService
{
    /**
     * The five proficiency bands, in the order the form prints them, with
     * their descriptive letter and inclusive average range.
     */
    public const BANDS = [
        'B' => ['label' => 'BEGINNING', 'note' => 'B: 74% and below', 'min' => 0, 'max' => 74],
        'D' => ['label' => 'DEVELOPING', 'note' => 'D: 75%–79%', 'min' => 75, 'max' => 79],
        'AP' => ['label' => 'APPROACHING PROFICIENCY', 'note' => 'AP: 80%–84%', 'min' => 80, 'max' => 84],
        'P' => ['label' => 'PROFICIENT', 'note' => 'P: 85%–89%', 'min' => 85, 'max' => 89],
        'A' => ['label' => 'ADVANCED', 'note' => 'A: 90% and above', 'min' => 90, 'max' => 100],
    ];

    /** An average at or above this prints to 3 decimals ("honor learner"). */
    public const HONOR_THRESHOLD = 90;

    public function build(Section $section): array
    {
        $section->loadMissing(['gradeLevel', 'adviser', 'schoolYear', 'school']);

        $enrollments = StudentEnrollment::with('student')
            ->where('section_id', $section->id)
            ->get();

        $males = $this->rows($this->sorted($enrollments, 'Male'));
        $females = $this->rows($this->sorted($enrollments, 'Female'));

        return [
            'section' => $section,
            'schoolYear' => $section->schoolYear,
            'school' => $section->school,
            'adviser' => $section->adviser?->full_name,
            'males' => $males,
            'females' => $females,
            'summary' => [
                'actions' => $this->actionSummary($males, $females),
                'proficiency' => $this->proficiencySummary($males, $females),
            ],
            'bands' => self::BANDS,
        ];
    }

    /** The ACTION TAKEN cell, derived from what the register already records. */
    public function action(StudentEnrollment $e): string
    {
        if ($e->is_irregular) {
            return '*IRREGULAR';
        }

        return match (true) {
            $e->promotion_status === 'retained' || $e->status === StudentEnrollment::STATUS_RETAINED => 'RETAINED',
            $e->promotion_status === 'promoted' || $e->promotion_status === 'graduated'
                || in_array($e->status, [StudentEnrollment::STATUS_PROMOTED, StudentEnrollment::STATUS_GRADUATED], true) => 'PROMOTED',
            default => '',
        };
    }

    /** Descriptive letter for an average, or '' when no average is recorded. */
    public function band(?float $average): string
    {
        if ($average === null) {
            return '';
        }

        // Compare against the next band's floor so fractional averages fall on
        // the right side: 74.5 is "74% and below" (B), not Developing.
        foreach (self::BANDS as $letter => $band) {
            if ($average < $band['max'] + 1) {
                return $letter;
            }
        }

        return 'A';
    }

    /**
     * "Numerical Value in 3 decimal places for honor learner, 2 for non-honor
     * & Descriptive Letter" — e.g. "92.375 (A)" or "83.50 (AP)".
     */
    public function formattedAverage(?float $average): string
    {
        if ($average === null) {
            return '';
        }

        $decimals = $average >= self::HONOR_THRESHOLD ? 3 : 2;

        return number_format($average, $decimals).' ('.$this->band($average).')';
    }

    private function sorted(Collection $enrollments, string $gender): Collection
    {
        return $enrollments
            ->filter(fn ($e) => $e->student?->gender === $gender)
            ->sortBy(fn ($e) => mb_strtolower($e->student->last_name.' '.$e->student->first_name.' '.$e->student->middle_name))
            ->values();
    }

    /** @return array<int, array<string, mixed>> */
    private function rows(Collection $enrollments): array
    {
        return $enrollments->map(fn ($e) => [
            'lrn' => $e->student->lrn,
            'name' => trim(collect([
                trim($e->student->last_name.' '.($e->student->suffix ?? '')),
                $e->student->first_name,
                $e->student->middle_name,
            ])->filter()->implode(', ')),
            'average' => $e->general_average !== null ? (float) $e->general_average : null,
            'average_display' => $this->formattedAverage($e->general_average !== null ? (float) $e->general_average : null),
            'action' => $this->action($e),
            'completed' => $e->subjects_completed,
            'incomplete' => $e->subjects_incomplete,
        ])->all();
    }

    /** PROMOTED / *IRREGULAR / RETAINED counts by sex. */
    private function actionSummary(array $males, array $females): array
    {
        $count = fn (array $rows, string $action) => count(array_filter($rows, fn ($r) => $r['action'] === $action));

        $summary = [];
        foreach (['PROMOTED', '*IRREGULAR', 'RETAINED'] as $action) {
            $m = $count($males, $action);
            $f = $count($females, $action);
            $summary[$action] = ['male' => $m, 'female' => $f, 'total' => $m + $f];
        }

        return $summary;
    }

    /** Learners per proficiency band by sex, from the recorded averages. */
    private function proficiencySummary(array $males, array $females): array
    {
        $count = fn (array $rows, string $letter) => count(array_filter(
            $rows, fn ($r) => $r['average'] !== null && $this->band($r['average']) === $letter
        ));

        $summary = [];
        foreach (array_keys(self::BANDS) as $letter) {
            $m = $count($males, $letter);
            $f = $count($females, $letter);
            $summary[$letter] = ['male' => $m, 'female' => $f, 'total' => $m + $f];
        }

        return $summary;
    }
}
