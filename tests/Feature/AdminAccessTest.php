<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    private function teacher(): User
    {
        return User::factory()->create(['role' => User::ROLE_TEACHER, 'is_active' => true]);
    }

    public function test_guest_is_redirected_to_login_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_teacher_is_forbidden_from_admin_area(): void
    {
        $this->actingAs($this->teacher())->get('/admin')->assertForbidden();
    }

    public function test_admin_can_load_every_index_and_create_page(): void
    {
        $admin = $this->admin();

        $routes = [
            'admin.dashboard',
            'admin.school-years.index', 'admin.school-years.create',
            'admin.grade-levels.index', 'admin.grade-levels.create',
            'admin.subjects.index', 'admin.subjects.create',
            'admin.teachers.index', 'admin.teachers.create',
            'admin.sections.index', 'admin.sections.create',
        ];

        foreach ($routes as $name) {
            $this->actingAs($admin)->get(route($name))
                ->assertOk("Route {$name} did not return 200");
        }
    }

    public function test_admin_can_create_a_grade_level(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.grade-levels.store'), [
                'name' => 'Grade 5', 'code' => 'G5', 'level_order' => 5, 'is_graduating' => 0,
            ])
            ->assertRedirect(route('admin.grade-levels.index'));

        $this->assertDatabaseHas('grade_levels', ['code' => 'G5', 'level_order' => 5]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'auditable_type' => GradeLevel::class]);
    }

    public function test_creating_a_school_year_generates_a_calendar(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.school-years.store'), [
                'name' => '2030-2031',
                'start_date' => '2030-06-03',
                'end_date' => '2030-06-14',
            ])->assertRedirect();

        $sy = SchoolYear::where('name', '2030-2031')->firstOrFail();
        // Two full weeks => 10 weekday class days, weekends excluded.
        $this->assertSame(10, $sy->calendarDays()->where('is_class_day', true)->count());
    }

    public function test_activating_a_school_year_deactivates_the_others(): void
    {
        $admin = $this->admin();
        $a = SchoolYear::create(['name' => '2028-2029', 'start_date' => '2028-06-01', 'end_date' => '2029-03-31', 'is_active' => true]);
        $b = SchoolYear::create(['name' => '2029-2030', 'start_date' => '2029-06-01', 'end_date' => '2030-03-31', 'is_active' => false]);

        $this->actingAs($admin)->post(route('admin.school-years.activate', $b))->assertRedirect();

        $this->assertFalse($a->fresh()->is_active);
        $this->assertTrue($b->fresh()->is_active);
    }

    public function test_admin_can_create_a_teacher_with_a_login_account(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.teachers.store'), [
                'first_name' => 'Juan', 'last_name' => 'Dela Cruz',
                'is_active' => 1,
                'create_account' => 1,
                'account_email' => 'juan@dpch.edu.ph',
                'account_password' => 'secret123',
            ])->assertRedirect(route('admin.teachers.index'));

        $this->assertDatabaseHas('users', ['email' => 'juan@dpch.edu.ph', 'role' => User::ROLE_TEACHER]);
        $teacher = Teacher::where('last_name', 'Dela Cruz')->firstOrFail();
        $this->assertNotNull($teacher->user_id);
    }
}
