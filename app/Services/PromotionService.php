<?php

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Advances learners from one school year to the next. Crucially, this NEVER
 * mutates or deletes prior enrollments — it creates brand-new enrollment rows
 * in the target year, so the full history is preserved. Graduating-grade
 * learners are marked graduated instead of promoted.
 */
class PromotionService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array<int, int|null>  $sectionMap  source_section_id => target_section_id
     * @return array{promoted:int, graduated:int, skipped:int, retained:int}
     */
    public function promote(SchoolYear $from, SchoolYear $to, array $sectionMap, User $admin): array
    {
        $result = ['promoted' => 0, 'graduated' => 0, 'skipped' => 0, 'retained' => 0];

        $enrollments = StudentEnrollment::with(['gradeLevel', 'student'])
            ->where('school_year_id', $from->id)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->get();

        // Learners already carrying an enrollment in the target year are left alone.
        $alreadyInTarget = StudentEnrollment::where('school_year_id', $to->id)
            ->pluck('student_id')->flip();

        DB::transaction(function () use ($enrollments, $from, $to, $sectionMap, $alreadyInTarget, &$result) {
            foreach ($enrollments as $enrollment) {
                if ($enrollment->promotion_status === StudentEnrollment::STATUS_RETAINED) {
                    $result['retained']++;

                    continue;
                }

                // Graduating grade -> graduate, no new enrollment.
                if ($enrollment->gradeLevel->is_graduating) {
                    $enrollment->update([
                        'status' => StudentEnrollment::STATUS_GRADUATED,
                        'promotion_status' => 'graduated',
                    ]);
                    $enrollment->student->update(['status' => 'graduated']);
                    $this->audit->log('graduated', $enrollment, "{$enrollment->student->full_name} graduated");
                    $result['graduated']++;

                    continue;
                }

                $nextGrade = $enrollment->gradeLevel->nextGrade();
                $targetSectionId = $sectionMap[$enrollment->section_id] ?? null;

                if (! $nextGrade || ! $targetSectionId || $alreadyInTarget->has($enrollment->student_id)) {
                    $result['skipped']++;

                    continue;
                }

                $targetSection = Section::find($targetSectionId);
                if (! $targetSection || $targetSection->school_year_id !== $to->id) {
                    $result['skipped']++;

                    continue;
                }

                // New enrollment in the target year — prior rows untouched.
                StudentEnrollment::create([
                    'student_id' => $enrollment->student_id,
                    'school_year_id' => $to->id,
                    'grade_level_id' => $targetSection->grade_level_id,
                    'section_id' => $targetSection->id,
                    'status' => StudentEnrollment::STATUS_ENROLLED,
                    'promotion_status' => 'pending',
                    'enrollment_date' => $to->start_date->toDateString(),
                ]);

                $enrollment->update(['promotion_status' => 'promoted']);
                $alreadyInTarget->put($enrollment->student_id, true);
                $result['promoted']++;
            }

            $this->audit->log('promotion', $from,
                "Promoted {$from->name} → {$to->name}: {$result['promoted']} promoted, {$result['graduated']} graduated");
        });

        return $result;
    }

    /**
     * Adviser-driven promotion: move hand-picked learners from the adviser's
     * old-year section into a target section (theirs, in the active year).
     * Same rules as promote() — graduating grades graduate instead, learners
     * already enrolled in the target year are skipped, history is preserved.
     *
     * @param  array<int>  $enrollmentIds  source StudentEnrollment ids
     * @return array{promoted:int, graduated:int, skipped:int}
     */
    public function promoteSelected(array $enrollmentIds, Section $target, User $actor): array
    {
        $result = ['promoted' => 0, 'graduated' => 0, 'skipped' => 0];

        $enrollments = StudentEnrollment::with(['gradeLevel', 'student'])
            ->whereIn('id', $enrollmentIds)
            ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
            ->get();

        $alreadyInTarget = StudentEnrollment::where('school_year_id', $target->school_year_id)
            ->pluck('student_id')->flip();

        DB::transaction(function () use ($enrollments, $target, &$result, $alreadyInTarget) {
            foreach ($enrollments as $enrollment) {
                if ($enrollment->gradeLevel->is_graduating) {
                    $enrollment->update([
                        'status' => StudentEnrollment::STATUS_GRADUATED,
                        'promotion_status' => 'graduated',
                    ]);
                    $enrollment->student->update(['status' => 'graduated']);
                    $this->audit->log('graduated', $enrollment, "{$enrollment->student->full_name} graduated");
                    $result['graduated']++;

                    continue;
                }

                if ($alreadyInTarget->has($enrollment->student_id)) {
                    $result['skipped']++;

                    continue;
                }

                StudentEnrollment::create([
                    'student_id' => $enrollment->student_id,
                    'school_year_id' => $target->school_year_id,
                    'grade_level_id' => $target->grade_level_id,
                    'section_id' => $target->id,
                    'status' => StudentEnrollment::STATUS_ENROLLED,
                    'promotion_status' => 'pending',
                    'enrollment_date' => $target->schoolYear->start_date->toDateString(),
                ]);
                $enrollment->update(['promotion_status' => 'promoted']);
                $alreadyInTarget->put($enrollment->student_id, true);
                $result['promoted']++;
            }

            $this->audit->log('promotion', $target,
                "Adviser promotion into {$target->name}: {$result['promoted']} promoted, {$result['graduated']} graduated, {$result['skipped']} skipped");
        });

        return $result;
    }
}
