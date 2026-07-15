<?php

namespace App\Services;

use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * Enroll a learner into a section. The section already encodes the school
     * year and grade level, so those are derived — never chosen separately.
     * A student may hold at most one enrollment per school year.
     */
    public function enroll(Student $student, Section $section, array $opts = []): StudentEnrollment
    {
        $existing = StudentEnrollment::where('student_id', $student->id)
            ->where('school_year_id', $section->school_year_id)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'student_id' => "{$student->full_name} is already enrolled this school year"
                    ." (Grade {$existing->gradeLevel?->name}, {$existing->section?->name}).",
            ]);
        }

        $enrollment = StudentEnrollment::create([
            'student_id' => $student->id,
            'school_year_id' => $section->school_year_id,
            'grade_level_id' => $section->grade_level_id,
            'section_id' => $section->id,
            'status' => $opts['status'] ?? StudentEnrollment::STATUS_ENROLLED,
            'promotion_status' => 'pending',
            'enrollment_date' => $opts['enrollment_date'] ?? Carbon::now()->toDateString(),
            'is_late_enrollment' => $opts['is_late_enrollment'] ?? false,
            'remarks' => $opts['remarks'] ?? null,
        ]);

        $this->audit->log('enrolled', $enrollment,
            "{$student->full_name} enrolled into {$section->gradeLevel?->name} - {$section->name}");

        return $enrollment;
    }

    /** Move a learner to another section within the SAME school year. */
    public function transfer(StudentEnrollment $enrollment, Section $newSection): StudentEnrollment
    {
        if ($newSection->school_year_id !== $enrollment->school_year_id) {
            throw ValidationException::withMessages([
                'section_id' => 'Transfers must stay within the same school year. Use promotion for a new year.',
            ]);
        }

        $original = $enrollment->getOriginal();
        $enrollment->update([
            'section_id' => $newSection->id,
            'grade_level_id' => $newSection->grade_level_id,
        ]);

        $this->audit->log('transferred', $enrollment,
            "Transferred to {$newSection->gradeLevel?->name} - {$newSection->name}", $original, $enrollment->getChanges());

        return $enrollment;
    }

    /** Change enrollment status (drop, transfer out, etc.). */
    public function changeStatus(StudentEnrollment $enrollment, string $status, ?string $remarks = null): StudentEnrollment
    {
        $original = $enrollment->getOriginal();
        $enrollment->update([
            'status' => $status,
            'remarks' => $remarks ?? $enrollment->remarks,
        ]);

        // Reflect terminal outcomes on the learner's global status.
        $studentStatus = match ($status) {
            StudentEnrollment::STATUS_TRANSFERRED_OUT => 'transferred',
            StudentEnrollment::STATUS_DROPPED => 'dropped',
            StudentEnrollment::STATUS_GRADUATED => 'graduated',
            default => null,
        };
        if ($studentStatus) {
            $enrollment->student()->update(['status' => $studentStatus]);
        }

        $this->audit->log('enrollment_status', $enrollment, "Enrollment status set to {$status}", $original, $enrollment->getChanges());

        return $enrollment;
    }
}
