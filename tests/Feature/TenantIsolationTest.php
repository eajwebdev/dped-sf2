<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Two schools share one database. A teacher must never be able to see, or
 * reach, anything belonging to another school.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function schoolWithClass(string $name): array
    {
        $school = School::factory()->create(['name' => $name]);

        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'school_id' => $school->id,
            'trial_ends_at' => now()->addDays(10),
        ]);

        $teacher = Teacher::factory()->create(['user_id' => $user->id, 'school_id' => $school->id]);

        // school_years and grade_levels carry no school_id — they are shared
        // reference data across every tenant.
        $year = SchoolYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create();

        $section = Section::factory()->create([
            'school_id' => $school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
        ]);

        $student = Student::factory()->create([
            'school_id' => $school->id,
            'last_name' => $name.'Learner',
        ]);

        return compact('school', 'user', 'teacher', 'section', 'student');
    }

    public function test_a_teacher_only_sees_their_own_schools_learners(): void
    {
        $a = $this->schoolWithClass('Alpha');
        $b = $this->schoolWithClass('Bravo');

        $this->actingAs($a['user']);

        $visible = Student::pluck('last_name')->all();

        $this->assertContains('AlphaLearner', $visible);
        $this->assertNotContains('BravoLearner', $visible, 'A teacher can read another school\'s learners.');
    }

    public function test_a_teacher_cannot_open_another_schools_section(): void
    {
        $a = $this->schoolWithClass('Alpha');
        $b = $this->schoolWithClass('Bravo');

        // Route-model binding must not resolve a section from another school.
        $this->actingAs($a['user'])
            ->get(route('attendance.sheet', $b['section']))
            ->assertNotFound();
    }

    public function test_a_teacher_cannot_open_another_schools_books_or_grades(): void
    {
        $a = $this->schoolWithClass('Alpha');
        $b = $this->schoolWithClass('Bravo');

        foreach (['books.index', 'reports.sf5.grades', 'insights.show'] as $route) {
            $this->actingAs($a['user'])
                ->get(route($route, $b['section']))
                ->assertNotFound();
        }
    }

    /**
     * The bypass the global scope leaves open by design: it filters only when
     * the acting user has a school_id. An account without one is unscoped and
     * reads every school in the database.
     */
    public function test_a_teacher_without_a_school_id_is_isolated_too(): void
    {
        $a = $this->schoolWithClass('Alpha');
        $b = $this->schoolWithClass('Bravo');

        $orphan = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'school_id' => null,
            'trial_ends_at' => now()->addDays(10),
        ]);

        $this->actingAs($orphan);

        $visible = Student::pluck('last_name')->all();

        $this->assertEmpty(
            array_intersect($visible, ['AlphaLearner', 'BravoLearner']),
            'A teacher with no school_id can read every school in the database.'
        );
    }

    public function test_an_admin_created_teacher_account_gets_a_school(): void
    {
        $a = $this->schoolWithClass('Alpha');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'school_id' => $a['school']->id,
        ]);

        $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'first_name' => 'Nina', 'last_name' => 'Cruz', 'gender' => 'Female',
            'employee_no' => 'T-99001', 'is_active' => '1',
            'create_account' => '1',
            'account_email' => 'nina@example.ph',
            'account_password' => 'password1234',
        ]);

        $created = User::where('email', 'nina@example.ph')->first();

        $this->assertNotNull($created, 'The teacher account was not created.');
        $this->assertSame($a['school']->id, $created->school_id,
            'An admin-created teacher must inherit the school, or it would be scoped to nothing.');
    }

    public function test_textbooks_carry_their_own_school_and_are_scoped(): void
    {
        $a = $this->schoolWithClass('Alpha');
        $b = $this->schoolWithClass('Bravo');

        $this->actingAs($a['user']);
        $book = \App\Models\Textbook::create([
            'section_id' => $a['section']->id,
            'subject_area' => 'Math',
            'title' => 'Alpha Algebra',
        ]);

        $this->assertSame($a['school']->id, $book->school_id, 'A textbook must be stamped with its school.');

        // The other school must not see it, even without going through a section.
        $this->actingAs($b['user']);
        $this->assertNull(\App\Models\Textbook::find($book->id));
    }
}
