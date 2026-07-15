<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    private function section(): Section
    {
        $year = SchoolYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7']);

        return Section::factory()->create([
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
        ]);
    }

    public function test_admin_can_create_a_student_with_photo_and_auto_qr(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.students.store'), [
            'lrn' => '123456789012',
            'first_name' => 'Jose', 'last_name' => 'Rizal',
            'gender' => 'Male', 'status' => 'active',
            'photo' => UploadedFile::fake()->image('jose.jpg'),
        ])->assertRedirect();

        $student = Student::where('lrn', '123456789012')->firstOrFail();
        $this->assertNotNull($student->qr_token, 'QR token should auto-generate');
        $this->assertNotNull($student->photo_path);
        Storage::disk('public')->assertExists($student->photo_path);
    }

    public function test_student_profile_page_renders_with_qr(): void
    {
        $student = Student::factory()->create();
        $this->actingAs($this->admin())->get(route('admin.students.show', $student))
            ->assertOk()
            ->assertSee($student->lrn);
    }

    public function test_admin_can_bulk_enroll_learners_into_a_section(): void
    {
        $section = $this->section();
        $students = Student::factory()->count(3)->create();

        $this->actingAs($this->admin())->post(route('admin.enrollments.store'), [
            'section_id' => $section->id,
            'student_ids' => $students->pluck('id')->all(),
        ])->assertRedirect();

        $this->assertSame(3, StudentEnrollment::where('section_id', $section->id)->count());
        $enrollment = StudentEnrollment::first();
        // Year + grade are derived from the section, never chosen separately.
        $this->assertSame($section->school_year_id, $enrollment->school_year_id);
        $this->assertSame($section->grade_level_id, $enrollment->grade_level_id);
    }

    public function test_a_learner_cannot_be_enrolled_twice_in_the_same_year(): void
    {
        $section = $this->section();
        $other = Section::factory()->create([
            'school_year_id' => $section->school_year_id,
            'grade_level_id' => $section->grade_level_id,
        ]);
        $student = Student::factory()->create();

        StudentEnrollment::create([
            'student_id' => $student->id,
            'school_year_id' => $section->school_year_id,
            'grade_level_id' => $section->grade_level_id,
            'section_id' => $section->id,
            'status' => 'enrolled', 'promotion_status' => 'pending',
            'enrollment_date' => now(),
        ]);

        // Attempting to enroll again into another section the same year is skipped.
        $this->actingAs($this->admin())->post(route('admin.enrollments.store'), [
            'section_id' => $other->id,
            'student_ids' => [$student->id],
        ])->assertRedirect();

        $this->assertSame(1, StudentEnrollment::where('student_id', $student->id)->count());
    }

    public function test_admin_can_transfer_a_learner_within_the_same_year(): void
    {
        $section = $this->section();
        $target = Section::factory()->create([
            'school_year_id' => $section->school_year_id,
            'grade_level_id' => $section->grade_level_id,
        ]);
        $student = Student::factory()->create();
        $enrollment = StudentEnrollment::create([
            'student_id' => $student->id,
            'school_year_id' => $section->school_year_id,
            'grade_level_id' => $section->grade_level_id,
            'section_id' => $section->id,
            'status' => 'enrolled', 'promotion_status' => 'pending',
            'enrollment_date' => now(),
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.enrollments.transfer', $enrollment), ['section_id' => $target->id])
            ->assertRedirect();

        $this->assertSame($target->id, $enrollment->fresh()->section_id);
    }

    public function test_admin_can_offer_a_subject_and_assign_a_teacher(): void
    {
        $section = $this->section();
        $subject = Subject::factory()->create();
        $teacher = Teacher::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.assignments.subjects.store', $section), ['subject_id' => $subject->id])
            ->assertRedirect();

        $offering = SubjectAssignment::where('section_id', $section->id)->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.assignments.teachers.store', $offering), ['teacher_id' => $teacher->id, 'is_primary' => 1])
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_subject_assignments', [
            'subject_assignment_id' => $offering->id,
            'teacher_id' => $teacher->id,
            'is_primary' => true,
        ]);
    }
}
