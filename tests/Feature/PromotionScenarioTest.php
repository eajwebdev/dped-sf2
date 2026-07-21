<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\GradeLevel;
use App\Models\LearnerGrade;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-of-year promotion, the real scenario: some learners move up, some are
 * retained, and the ones who move up can land under a DIFFERENT adviser — all
 * while last year's records are never touched.
 */
class PromotionScenarioTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private SchoolYear $oldYear;
    private SchoolYear $newYear;
    private GradeLevel $g8;
    private GradeLevel $g9;
    private Teacher $adviserA; // last year's Grade 8 adviser
    private Teacher $adviserB; // this year's Grade 9 adviser (a different teacher)
    private User $adviserAUser;
    private User $adviserBUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create(['name' => 'Test NHS']);

        $this->oldYear = SchoolYear::factory()->create(['name' => '2025-2026', 'is_active' => false, 'start_date' => '2025-06-01', 'end_date' => '2026-03-31']);
        $this->newYear = SchoolYear::factory()->active()->create(['name' => '2026-2027', 'start_date' => '2026-06-01', 'end_date' => '2027-03-31']);

        $this->g8 = GradeLevel::factory()->create(['code' => 'G8', 'level_order' => 8, 'is_graduating' => false]);
        $this->g9 = GradeLevel::factory()->create(['code' => 'G9', 'level_order' => 9, 'is_graduating' => false]);

        [$this->adviserAUser, $this->adviserA] = $this->teacher('adviser.a@test.ph');
        [$this->adviserBUser, $this->adviserB] = $this->teacher('adviser.b@test.ph');
    }

    /** @return array{0: User, 1: Teacher} */
    private function teacher(string $email): array
    {
        $user = User::factory()->create([
            'email' => $email, 'role' => User::ROLE_TEACHER, 'is_active' => true,
            'status' => User::STATUS_APPROVED, 'school_id' => $this->school->id,
            'trial_ends_at' => now()->addDays(10),
        ]);
        $teacher = Teacher::factory()->create(['user_id' => $user->id, 'school_id' => $this->school->id]);

        return [$user, $teacher];
    }

    private function enrollInto(Section $section, string $lastName): StudentEnrollment
    {
        $student = Student::factory()->create(['last_name' => $lastName, 'school_id' => $this->school->id]);

        return StudentEnrollment::create([
            'student_id' => $student->id,
            'school_year_id' => $section->school_year_id,
            'grade_level_id' => $section->grade_level_id,
            'section_id' => $section->id,
            'status' => StudentEnrollment::STATUS_ENROLLED,
            'promotion_status' => 'pending',
            'enrollment_date' => $section->schoolYear->start_date,
            'school_id' => $this->school->id,
        ]);
    }

    public function test_year_end_promotion_moves_some_up_retains_others_under_a_new_adviser(): void
    {
        // Last year: adviser A's Grade 8 class with three learners.
        $oldSection = Section::factory()->create([
            'school_id' => $this->school->id, 'school_year_id' => $this->oldYear->id,
            'grade_level_id' => $this->g8->id, 'adviser_id' => $this->adviserA->id, 'name' => 'JADEITE',
        ]);

        $mover1 = $this->enrollInto($oldSection, 'Mover-One');
        $mover2 = $this->enrollInto($oldSection, 'Mover-Two');
        $stayer = $this->enrollInto($oldSection, 'Stayer');

        // Last year's records that must survive untouched.
        LearnerGrade::create(['school_id' => $this->school->id, 'student_enrollment_id' => $mover1->id, 'subject_id' => \App\Models\Subject::factory()->create()->id, 'period' => 1, 'grade' => 90]);
        Attendance::create(['school_id' => $this->school->id, 'student_enrollment_id' => $mover1->id, 'student_id' => $mover1->student_id, 'section_id' => $oldSection->id, 'school_year_id' => $this->oldYear->id, 'attendance_date' => '2025-07-01', 'status' => 'present']);

        // The Stayer is flagged to repeat the grade — they must NOT move up.
        $stayer->update(['promotion_status' => StudentEnrollment::STATUS_RETAINED]);

        // This year: a DIFFERENT teacher (adviser B) advises the Grade 9 class the movers go into.
        $newSection = Section::factory()->create([
            'school_id' => $this->school->id, 'school_year_id' => $this->newYear->id,
            'grade_level_id' => $this->g9->id, 'adviser_id' => $this->adviserB->id, 'name' => 'EMERALD',
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true, 'status' => User::STATUS_APPROVED, 'school_id' => null]);

        // Promote: map last year's Grade 8 section into this year's Grade 9 section.
        $result = app(PromotionService::class)->promote(
            $this->oldYear, $this->newYear, [$oldSection->id => $newSection->id], $admin
        );

        // ---- Counts ---------------------------------------------------------
        $this->assertSame(2, $result['promoted'], 'Both movers should advance.');
        $this->assertSame(1, $result['retained'], 'The retained learner should not advance.');

        // ---- Moving up, under the NEW adviser -------------------------------
        foreach ([$mover1, $mover2] as $mover) {
            $newEnrollment = StudentEnrollment::where('student_id', $mover->student_id)
                ->where('school_year_id', $this->newYear->id)->first();

            $this->assertNotNull($newEnrollment, 'A promoted learner must have a new enrolment this year.');
            $this->assertSame($this->g9->id, $newEnrollment->grade_level_id, 'They advance to Grade 9.');
            $this->assertSame($newSection->id, $newEnrollment->section_id);
            $this->assertSame(StudentEnrollment::STATUS_ENROLLED, $newEnrollment->status);
        }
        // The new section is advised by a different teacher than last year.
        $this->assertNotSame($this->adviserA->id, $newSection->adviser_id);
        $this->assertSame($this->adviserB->id, $newSection->fresh()->adviser_id);

        // ---- NOT moving up --------------------------------------------------
        $this->assertNull(
            StudentEnrollment::where('student_id', $stayer->student_id)->where('school_year_id', $this->newYear->id)->first(),
            'A retained learner must NOT get a new enrolment.'
        );

        // ---- Previous records preserved & untouched -------------------------
        // All three old enrolments still exist, still in last year's Grade 8 section.
        foreach ([$mover1, $mover2, $stayer] as $old) {
            $old->refresh();
            $this->assertSame($this->oldYear->id, $old->school_year_id);
            $this->assertSame($oldSection->id, $old->section_id);
            $this->assertSame($this->g8->id, $old->grade_level_id);
        }
        // Old grade + attendance rows are still attached to the OLD enrolment.
        $this->assertDatabaseHas('learner_grades', ['student_enrollment_id' => $mover1->id, 'grade' => 90]);
        $this->assertDatabaseHas('attendance', ['student_enrollment_id' => $mover1->id, 'section_id' => $oldSection->id, 'status' => 'present']);
        // Promotion only stamped a marker on the old rows, it did not move them.
        $this->assertSame('promoted', $mover1->promotion_status);
        $this->assertSame(StudentEnrollment::STATUS_RETAINED, $stayer->fresh()->promotion_status);

        // ---- Last year's adviser can still reach last year's class ----------
        $this->assertTrue(
            $this->adviserAUser->advisorySections()->whereKey($oldSection->id)->exists(),
            "Last year's adviser must still see last year's class in reports."
        );

        // ---- This year's adviser sees exactly the two promoted learners -----
        $bActive = $this->adviserBUser->advisorySections()
            ->whereKey($newSection->id)->first()
            ->activeEnrollments()->count();
        $this->assertSame(2, $bActive, "This year's adviser should see the two promoted learners.");
    }

    public function test_promotion_can_target_a_next_grade_section_that_has_no_adviser_yet(): void
    {
        $oldSection = Section::factory()->create([
            'school_id' => $this->school->id, 'school_year_id' => $this->oldYear->id,
            'grade_level_id' => $this->g8->id, 'adviser_id' => $this->adviserA->id, 'name' => 'RUBY',
        ]);
        $mover = $this->enrollInto($oldSection, 'Solo-Mover');

        // A Grade 9 section for the new year that has NOT been assigned an adviser yet.
        $unadvised = Section::factory()->create([
            'school_id' => $this->school->id, 'school_year_id' => $this->newYear->id,
            'grade_level_id' => $this->g9->id, 'adviser_id' => null, 'name' => 'PENDING-ADVISER',
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true, 'status' => User::STATUS_APPROVED, 'school_id' => null]);

        $result = app(PromotionService::class)->promote(
            $this->oldYear, $this->newYear, [$oldSection->id => $unadvised->id], $admin
        );

        $this->assertSame(1, $result['promoted']);

        $newEnrollment = StudentEnrollment::where('student_id', $mover->student_id)
            ->where('school_year_id', $this->newYear->id)->first();

        // The learner moves up fine even though the section has no adviser assigned yet.
        $this->assertNotNull($newEnrollment, 'The learner still advances into an unadvised section.');
        $this->assertSame($unadvised->id, $newEnrollment->section_id);
        $this->assertNull($unadvised->fresh()->adviser_id, 'The section stays unadvised until a teacher is assigned.');
    }
}
