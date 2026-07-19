<?php

namespace App\Services;

use App\Models\Section;
use App\Models\StudentEnrollment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the data behind the DepEd School Form 1 (SF1) — School Register —
 * for one section in one school year.
 *
 * The register lists every learner ever enrolled in the section for the year
 * (not just the currently active ones), because transferred-out and dropped
 * learners still occupy a numbered row with the matching REMARKS code.
 */
class Sf1ReportService
{
    /**
     * The eight indicator codes from the SF1 legend, in the order the form
     * prints them, with the "required information" each one demands.
     */
    public const INDICATORS = [
        'T/O' => 'Transferred Out',
        'T/I' => 'Transferred IN',
        'DRP' => 'Dropped',
        'LE' => 'Late Enrollment',
        'CCT' => 'CCT Recipient',
        'B/A' => 'Balik-Aral',
        'LWD' => 'Learner With Disability',
        'ACL' => 'Accelerated',
    ];

    public function build(Section $section): array
    {
        $section->loadMissing(['gradeLevel', 'adviser', 'schoolYear', 'school']);

        $schoolYear = $section->schoolYear;
        $cutOff = $this->firstFridayOfJune($schoolYear?->start_date);

        $enrollments = StudentEnrollment::with('student')
            ->where('section_id', $section->id)
            ->get();

        $males = $this->rows($this->sorted($enrollments, 'Male'), $cutOff);
        $females = $this->rows($this->sorted($enrollments, 'Female'), $cutOff);

        return [
            'section' => $section,
            'schoolYear' => $schoolYear,
            'school' => \App\Support\ReportSchool::for($section),
            'adviser' => $section->adviser?->full_name,
            'cutOff' => $cutOff,
            'males' => $males,
            'females' => $females,
            'summary' => $this->summary($enrollments),
            'indicators' => self::INDICATORS,
        ];
    }

    /**
     * The SF1 age cut-off is the first Friday of June in the school year's
     * opening year. Falls back to the current year when no year is set.
     */
    public function firstFridayOfJune(?Carbon $yearStart): Carbon
    {
        $year = $yearStart?->year ?? now()->year;

        return Carbon::create($year, 6, 1)->startOfDay()->firstOfMonth(Carbon::FRIDAY);
    }

    /** One gender's learners, ordered Last, First as the register requires. */
    private function sorted(Collection $enrollments, string $gender): Collection
    {
        return $enrollments
            ->filter(fn ($e) => $e->student?->gender === $gender)
            ->sortBy(fn ($e) => mb_strtolower($e->student->last_name.' '.$e->student->first_name.' '.$e->student->middle_name))
            ->values();
    }

    /** @return array<int, array<string, mixed>> */
    private function rows(Collection $enrollments, Carbon $cutOff): array
    {
        $rows = [];
        $no = 1;

        foreach ($enrollments as $enrollment) {
            $s = $enrollment->student;

            $rows[] = [
                'no' => $no++,
                'lrn' => $s->lrn,
                'name' => $this->registerName($s),
                'sex' => $s->gender === 'Male' ? 'M' : 'F',
                'birthdate' => $s->birthdate?->format('m/d/y'),
                'age' => $s->ageAsOf($cutOff),
                'birth_place' => $s->birth_place,
                'mother_tongue' => $s->mother_tongue,
                'ethnic_group' => $s->ethnic_group,
                'religion' => $s->religion,
                'street' => $s->address_street ?: $s->address,   // legacy single-line fallback
                'barangay' => $s->address_barangay,
                'municipality' => $s->address_municipality,
                'province' => $s->address_province,
                'father' => $s->father_name,
                'mother' => $s->mother_name,
                'guardian' => $s->guardian_name,
                'relationship' => $s->guardian_relationship,
                'contact' => $s->guardian_contact,
                'remarks' => $this->remarks($enrollment),
            ];
        }

        return $rows;
    }

    /** SF1 prints "Last Name, First Name, Middle Name" in a single column. */
    private function registerName($student): string
    {
        $last = trim($student->last_name.' '.($student->suffix ?? ''));

        return trim(collect([$last, $student->first_name, $student->middle_name])
            ->filter()
            ->implode(', '));
    }

    /**
     * The REMARKS cell: every applicable indicator code followed by the detail
     * its legend entry requires, separated by "; " when a learner has several.
     */
    private function remarks(StudentEnrollment $e): string
    {
        $parts = [];

        $withDate = fn (?string $text, ?Carbon $date) => trim(implode(' ', array_filter([
            $text, $date?->format('m/d/Y'),
        ])));

        if ($e->status === StudentEnrollment::STATUS_TRANSFERRED_OUT) {
            $parts[] = trim('T/O '.$withDate($e->transfer_school, $e->transfer_date));
        }
        if ($e->status === StudentEnrollment::STATUS_TRANSFERRED_IN) {
            $parts[] = trim('T/I '.$withDate($e->transfer_school, $e->transfer_date));
        }
        if ($e->status === StudentEnrollment::STATUS_DROPPED) {
            $parts[] = trim('DRP '.$withDate($e->dropped_reason, $e->dropped_date));
        }
        if ($e->is_late_enrollment) {
            $parts[] = trim('LE '.(string) $e->late_enrollment_reason);
        }
        if (filled($e->cct_reference)) {
            $parts[] = 'CCT '.$e->cct_reference;
        }
        if (filled($e->balik_aral_detail)) {
            $parts[] = 'B/A '.$e->balik_aral_detail;
        }
        if (filled($e->disability_detail)) {
            $parts[] = 'LWD '.$e->disability_detail;
        }
        if (filled($e->accelerated_detail)) {
            $parts[] = 'ACL '.$e->accelerated_detail;
        }
        if (filled($e->remarks)) {
            $parts[] = $e->remarks;
        }

        return implode('; ', array_filter($parts));
    }

    /**
     * The BoSY / EoSY registration table. Beginning of school year counts every
     * learner who was on the register at the start (i.e. not a late enrolment);
     * end of school year counts those still actively enrolled.
     */
    private function summary(Collection $enrollments): array
    {
        $bosy = $enrollments->filter(fn ($e) => ! $e->is_late_enrollment
            && $e->status !== StudentEnrollment::STATUS_TRANSFERRED_IN);

        $eosy = $enrollments->filter(fn ($e) => in_array($e->status, [
            StudentEnrollment::STATUS_ENROLLED,
            StudentEnrollment::STATUS_TRANSFERRED_IN,
            StudentEnrollment::STATUS_PROMOTED,
            StudentEnrollment::STATUS_RETAINED,
            StudentEnrollment::STATUS_GRADUATED,
        ], true));

        $count = fn (Collection $c, string $gender) => $c->filter(fn ($e) => $e->student?->gender === $gender)->count();

        $bosyM = $count($bosy, 'Male');
        $bosyF = $count($bosy, 'Female');
        $eosyM = $count($eosy, 'Male');
        $eosyF = $count($eosy, 'Female');

        return [
            'male' => ['bosy' => $bosyM, 'eosy' => $eosyM],
            'female' => ['bosy' => $bosyF, 'eosy' => $eosyF],
            'total' => ['bosy' => $bosyM + $bosyF, 'eosy' => $eosyM + $eosyF],
        ];
    }
}
