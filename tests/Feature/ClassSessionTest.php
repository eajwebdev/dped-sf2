<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClassSessionTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $teacherUser;

    private Section $section;

    private Student $student;

    private StudentEnrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $year = SchoolYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create();

        $this->teacherUser = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'school_id' => $this->school->id,
            'trial_ends_at' => Carbon::now()->addDays(10),
        ]);
        $teacher = Teacher::factory()->create([
            'user_id' => $this->teacherUser->id,
            'school_id' => $this->school->id,
        ]);

        $this->section = Section::factory()->create([
            'school_id' => $this->school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
        ]);

        $this->student = Student::factory()->create(['school_id' => $this->school->id, 'qr_token' => 'TOKEN-ABC']);
        $this->enrollment = StudentEnrollment::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'section_id' => $this->section->id,
            'status' => StudentEnrollment::STATUS_ENROLLED,
            'enrollment_date' => Carbon::today(),
        ]);
    }

    public function test_starting_a_class_creates_a_session_and_seeds_absent(): void
    {
        $this->actingAs($this->teacherUser)
            ->post(route('class-sessions.start'), ['section_id' => $this->section->id])
            ->assertRedirect();

        $session = ClassSession::withoutGlobalScopes()->firstOrFail();
        $this->assertSame(ClassSession::STATUS_ACTIVE, $session->status);
        $this->assertNotEmpty($session->qr_key);

        // Every enrolled learner is seeded absent for today.
        $this->assertDatabaseHas('attendance', [
            'student_enrollment_id' => $this->enrollment->id,
            'status' => 'absent',
        ]);
    }

    public function test_starting_twice_resumes_the_same_session(): void
    {
        $this->actingAs($this->teacherUser)->post(route('class-sessions.start'), ['section_id' => $this->section->id]);
        $this->actingAs($this->teacherUser)->post(route('class-sessions.start'), ['section_id' => $this->section->id]);

        $this->assertSame(1, ClassSession::withoutGlobalScopes()->count());
    }

    public function test_scanner_unlocks_with_key_and_marks_present(): void
    {
        $this->actingAs($this->teacherUser)->post(route('class-sessions.start'), ['section_id' => $this->section->id]);
        $session = ClassSession::withoutGlobalScopes()->firstOrFail();

        // A fresh guest (the assigned scanner) unlocks with the key.
        $this->post(route('class-scan.unlock'), ['qr_key' => $session->qr_key])
            ->assertRedirect(route('class-scan.show'));

        // Scanning the learner's token flips them to present.
        $this->postJson(route('class-scan.checkin'), ['token' => 'TOKEN-ABC'])
            ->assertOk()
            ->assertJson(['ok' => true, 'status' => 'present']);

        $this->assertDatabaseHas('attendance', [
            'student_enrollment_id' => $this->enrollment->id,
            'status' => 'present',
        ]);
    }

    public function test_invalid_key_is_rejected(): void
    {
        $this->post(route('class-scan.unlock'), ['qr_key' => 'NOPE99'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->get(route('class-scan.show'))->assertRedirect(route('class-scan.enter'));
    }

    public function test_ended_session_key_stops_working(): void
    {
        $this->actingAs($this->teacherUser)->post(route('class-sessions.start'), ['section_id' => $this->section->id]);
        $session = ClassSession::withoutGlobalScopes()->firstOrFail();

        $this->actingAs($this->teacherUser)->post(route('class-sessions.end', $session))->assertRedirect();

        $this->post(route('class-scan.unlock'), ['qr_key' => $session->qr_key])
            ->assertSessionHas('error');
    }

    public function test_teacher_cannot_start_a_class_for_an_unrelated_section(): void
    {
        $otherSection = Section::factory()->create([
            'school_id' => $this->school->id,
            'school_year_id' => $this->section->school_year_id,
            'grade_level_id' => $this->section->grade_level_id,
        ]);

        $this->actingAs($this->teacherUser)
            ->post(route('class-sessions.start'), ['section_id' => $otherSection->id])
            ->assertNotFound();
    }
}
