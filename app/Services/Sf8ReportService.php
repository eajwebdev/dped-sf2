<?php

namespace App\Services;

use App\Models\Section;
use App\Models\StudentEnrollment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the data behind the DepEd School Form 8 (SF8) — Learner's Basic
 * Health and Nutrition Report — for one section in one school year.
 *
 * The system does not store weight/height measurements, so the form prints
 * the learner roster (No., LRN, Name, Birthdate, Age) with the measurement
 * columns — Weight, Height, Height², BMI, BMI Category, HFA — left blank,
 * exactly as the official template is handed out for the weighing session.
 */
class Sf8ReportService
{
    public function build(Section $section, ?Carbon $assessmentDate = null): array
    {
        $section->loadMissing(['gradeLevel', 'adviser', 'schoolYear', 'school']);

        // Age on the SF8 is the learner's age when measured; without a stated
        // assessment date, today is the closest truthful reckoning point.
        $asOf = $assessmentDate ?? now();

        $enrollments = StudentEnrollment::with('student')
            ->where('section_id', $section->id)
            ->whereIn('status', [
                StudentEnrollment::STATUS_ENROLLED,
                StudentEnrollment::STATUS_TRANSFERRED_IN,
                StudentEnrollment::STATUS_PROMOTED,
                StudentEnrollment::STATUS_RETAINED,
                StudentEnrollment::STATUS_GRADUATED,
            ])
            ->get();

        return [
            'section' => $section,
            'schoolYear' => $section->schoolYear,
            'school' => \App\Support\ReportSchool::for($section),
            'adviser' => $section->adviser?->full_name,
            'males' => $this->rows($this->sorted($enrollments, 'Male'), $asOf),
            'females' => $this->rows($this->sorted($enrollments, 'Female'), $asOf),
        ];
    }

    /** One sex's learners, alphabetical Last, First — numbering restarts per block. */
    private function sorted(Collection $enrollments, string $gender): Collection
    {
        return $enrollments
            ->filter(fn ($e) => $e->student?->gender === $gender)
            ->sortBy(fn ($e) => mb_strtolower($e->student->last_name.' '.$e->student->first_name.' '.$e->student->middle_name))
            ->values();
    }

    /** @return array<int, array<string, mixed>> */
    private function rows(Collection $enrollments, Carbon $asOf): array
    {
        $rows = [];
        $no = 1;

        foreach ($enrollments as $enrollment) {
            $s = $enrollment->student;

            $rows[] = [
                'no' => $no++,
                'lrn' => $s->lrn,
                'name' => $this->learnerName($s),
                'birthdate' => $s->birthdate?->format('m/d/Y'),
                'age' => $s->ageAsOf($asOf),
            ];
        }

        return $rows;
    }

    /** SF8 prints "Last Name, First Name, Name Extension, Middle Name". */
    private function learnerName($student): string
    {
        return trim(collect([
            $student->last_name,
            $student->first_name,
            $student->suffix,
            $student->middle_name,
        ])->filter()->implode(', '));
    }
}
