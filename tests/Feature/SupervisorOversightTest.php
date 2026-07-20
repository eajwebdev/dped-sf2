<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The read-only School Head (supervisor) role. It must see every class in its
 * OWN school and nothing in any other, and must never be able to write —
 * neither through the teacher app nor the admin area.
 */
class SupervisorOversightTest extends TestCase
{
    use RefreshDatabase;

    /** A school with one advisory class, one enrolled learner, and a supervisor. */
    private function schoolWithClass(string $name): array
    {
        $school = School::factory()->create(['name' => $name]);

        $teacherUser = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'school_id' => $school->id,
            'trial_ends_at' => now()->addDays(10),
        ]);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id, 'school_id' => $school->id]);

        $year = SchoolYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create();

        $section = Section::factory()->create([
            'school_id' => $school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
            'name' => $name.' Class',
        ]);

        $student = Student::factory()->create([
            'school_id' => $school->id,
            'last_name' => $name.'Learner',
        ]);

        StudentEnrollment::create([
            'student_id' => $student->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'section_id' => $section->id,
            'status' => StudentEnrollment::STATUS_ENROLLED,
            'enrollment_date' => $year->start_date,
            'school_id' => $school->id,
        ]);

        $supervisor = User::factory()->create([
            'role' => User::ROLE_SUPERVISOR,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'school_id' => $school->id,
        ]);

        return compact('school', 'teacher', 'section', 'student', 'supervisor', 'year');
    }

    // ── Registration & approval ──────────────────────────────────────────────

    public function test_a_school_head_can_self_register_as_pending(): void
    {
        Storage::fake('local');
        $school = School::factory()->create();

        $this->post('/register', [
            'role' => 'supervisor',
            'name' => 'Principal Reyes',
            'email' => 'principal@example.com',
            'contact_number' => '09171234567',
            'school_id' => $school->id,
            'school_id_number' => '2010-0001',
            'school_id_document' => UploadedFile::fake()->image('id.jpg', 800, 600)->size(500),
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
        ])->assertRedirect(route('account.pending'));

        $this->assertDatabaseHas('users', [
            'email' => 'principal@example.com',
            'role' => User::ROLE_SUPERVISOR,
            'status' => User::STATUS_PENDING,
            'school_id' => $school->id,
        ]);
    }

    public function test_registration_never_grants_admin_through_the_public_form(): void
    {
        Storage::fake('local');
        $school = School::factory()->create();

        $this->post('/register', [
            'role' => 'admin', // attempt to self-provision an admin
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'contact_number' => '09171234567',
            'school_id' => $school->id,
            'school_id_number' => '2010-0002',
            'school_id_document' => UploadedFile::fake()->image('id.jpg', 800, 600)->size(500),
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
        ]);

        // The controller validates role in {teacher, supervisor}; an unknown
        // role must be rejected, never silently upgraded to admin.
        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com', 'role' => User::ROLE_ADMIN]);
    }

    public function test_approving_a_supervisor_grants_access_without_trial_or_teacher_record(): void
    {
        $school = School::factory()->create();
        $pending = User::factory()->create([
            'role' => User::ROLE_SUPERVISOR,
            'status' => User::STATUS_PENDING,
            'school_id' => $school->id,
        ]);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'school_id' => null,
        ]);

        $this->actingAs($admin)->post(route('admin.registrations.approve', $pending))->assertRedirect();

        $pending->refresh();
        $this->assertSame(User::STATUS_APPROVED, $pending->status);
        $this->assertNull($pending->trial_ends_at, 'A supervisor must not be put on a billing trial.');
        $this->assertFalse($pending->teacher()->exists(), 'A supervisor must not get a Teacher record.');
        $this->assertTrue($pending->hasActiveAccess(), 'An approved supervisor should have access.');
        $this->assertSame('managed', $pending->subscriptionState());
    }

    // ── Read access, own school only ─────────────────────────────────────────

    public function test_an_approved_supervisor_reaches_their_oversight_dashboard(): void
    {
        $a = $this->schoolWithClass('Alpha');

        $this->actingAs($a['supervisor'])->get(route('supervisor.dashboard'))->assertOk();
    }

    public function test_a_supervisor_can_view_their_schools_sf2_pdf(): void
    {
        $a = $this->schoolWithClass('Alpha');

        // No year/month: the controller resolves them from the section's own
        // school year, exercising the real default path.
        $response = $this->actingAs($a['supervisor'])
            ->get(route('supervisor.sf2.show', $a['section']));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_a_supervisor_can_open_their_schools_insights_dashboard(): void
    {
        $a = $this->schoolWithClass('Alpha');

        $this->actingAs($a['supervisor'])
            ->get(route('supervisor.insights.show', $a['section']))
            ->assertOk();
    }

    public function test_overseeable_sections_are_limited_to_the_supervisors_school(): void
    {
        $a = $this->schoolWithClass('Alpha');
        $b = $this->schoolWithClass('Bravo');

        $this->actingAs($a['supervisor']);
        $ids = $a['supervisor']->overseeableSections()->pluck('id')->all();

        $this->assertContains($a['section']->id, $ids);
        $this->assertNotContains($b['section']->id, $ids, 'A supervisor can see another school\'s class.');
    }

    // ── Cross-tenant isolation ───────────────────────────────────────────────

    public function test_a_supervisor_cannot_open_another_schools_class(): void
    {
        $a = $this->schoolWithClass('Alpha');
        $b = $this->schoolWithClass('Bravo');

        foreach (['supervisor.sf2.show', 'supervisor.sf1.show', 'supervisor.insights.show'] as $route) {
            $this->actingAs($a['supervisor'])
                ->get(route($route, $b['section']))
                ->assertNotFound();
        }
    }

    // ── No write access anywhere ─────────────────────────────────────────────

    public function test_a_supervisor_is_bounced_out_of_the_teacher_app(): void
    {
        $a = $this->schoolWithClass('Alpha');

        $this->actingAs($a['supervisor'])
            ->get(route('teacher.dashboard'))
            ->assertRedirect(route('supervisor.dashboard'));
    }

    public function test_a_supervisor_cannot_save_attendance(): void
    {
        $a = $this->schoolWithClass('Alpha');

        // Even aimed at their own school's section, the write is refused: the
        // subscription middleware bounces supervisors before the controller.
        $this->actingAs($a['supervisor'])
            ->post(route('attendance.save', $a['section']), ['marks' => []])
            ->assertRedirect(route('supervisor.dashboard'));

        $this->assertDatabaseCount('attendance', 0);
    }

    public function test_a_supervisor_cannot_reach_the_admin_area(): void
    {
        $a = $this->schoolWithClass('Alpha');

        $this->actingAs($a['supervisor'])->get(route('admin.dashboard'))->assertForbidden();
    }
}
