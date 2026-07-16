<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SchoolScopingTest extends TestCase
{
    use RefreshDatabase;

    /** An approved, on-trial teacher belonging to a school. */
    private function teacherFor(School $school): User
    {
        return User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'school_id' => $school->id,
            'trial_ends_at' => Carbon::now()->addDays(10),
        ]);
    }

    public function test_teacher_can_add_a_student_scoped_to_their_school(): void
    {
        $school = School::factory()->create();
        $teacher = $this->teacherFor($school);

        $this->actingAs($teacher)->post(route('teacher.students.store'), [
            'lrn' => '123456789012',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'gender' => 'Female',
            'status' => 'active',
        ])->assertRedirect(route('teacher.students.index'));

        $this->assertDatabaseHas('students', [
            'lrn' => '123456789012',
            'school_id' => $school->id,
        ]);
    }

    public function test_a_teacher_only_sees_their_own_schools_students(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacherA = $this->teacherFor($schoolA);
        $teacherB = $this->teacherFor($schoolB);

        // Create one student in each school (auto-stamped by the acting user).
        $this->actingAs($teacherA)->post(route('teacher.students.store'), [
            'lrn' => '111111111111', 'first_name' => 'Aaa', 'last_name' => 'Alpha', 'gender' => 'Male', 'status' => 'active',
        ]);
        $this->actingAs($teacherB)->post(route('teacher.students.store'), [
            'lrn' => '222222222222', 'first_name' => 'Bbb', 'last_name' => 'Beta', 'gender' => 'Male', 'status' => 'active',
        ]);

        $this->actingAs($teacherA)->get(route('teacher.students.index'))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Beta');
    }

    public function test_a_teacher_cannot_edit_another_schools_student(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacherA = $this->teacherFor($schoolA);
        $teacherB = $this->teacherFor($schoolB);

        // Student owned by school B (created while acting as teacher B).
        $this->actingAs($teacherB)->post(route('teacher.students.store'), [
            'lrn' => '333333333333', 'first_name' => 'Ccc', 'last_name' => 'Gamma', 'gender' => 'Male', 'status' => 'active',
        ]);
        $studentB = Student::withoutGlobalScopes()->where('lrn', '333333333333')->firstOrFail();

        // Teacher A's scoped route-model binding should not resolve it.
        $this->actingAs($teacherA)->get(route('teacher.students.edit', $studentB))->assertNotFound();
    }

    public function test_two_schools_can_reuse_the_same_subject_code(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $this->actingAs($this->teacherFor($schoolA))->post(route('teacher.subjects.store'), [
            'name' => 'Mathematics', 'code' => 'MATH7', 'is_active' => 1,
        ])->assertRedirect(route('teacher.subjects.index'));

        // Same code in a different school must be allowed.
        $this->actingAs($this->teacherFor($schoolB))->post(route('teacher.subjects.store'), [
            'name' => 'Mathematics', 'code' => 'MATH7', 'is_active' => 1,
        ])->assertRedirect(route('teacher.subjects.index'));

        $this->assertSame(2, Subject::withoutGlobalScopes()->where('code', 'MATH7')->count());
    }

    public function test_duplicate_subject_code_within_a_school_is_rejected(): void
    {
        $school = School::factory()->create();
        $teacher = $this->teacherFor($school);

        $this->actingAs($teacher)->post(route('teacher.subjects.store'), [
            'name' => 'Science', 'code' => 'SCI7', 'is_active' => 1,
        ]);

        $this->actingAs($teacher)->post(route('teacher.subjects.store'), [
            'name' => 'Science II', 'code' => 'SCI7', 'is_active' => 1,
        ])->assertSessionHasErrors('code');
    }
}
