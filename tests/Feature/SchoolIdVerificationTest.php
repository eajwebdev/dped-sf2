<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Registration is the only unauthenticated write path in the app and it now
 * accepts a file, so these tests pin down both the happy path and the ways an
 * attacker would try to abuse it.
 */
class SchoolIdVerificationTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->school = School::factory()->create(['is_active' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'contact_number' => '09171234567',
            'school_id' => $this->school->id,
            'school_id_number' => '2019-04821',
            'school_id_document' => UploadedFile::fake()->image('id.jpg', 800, 600)->size(500),
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
        ], $overrides);
    }

    public function test_a_teacher_registers_with_a_school_id_and_lands_pending(): void
    {
        $this->post(route('register'), $this->payload())->assertRedirect(route('account.pending'));

        $user = User::where('email', 'juan@example.com')->firstOrFail();

        $this->assertSame(User::STATUS_PENDING, $user->status);
        $this->assertSame('2019-04821', $user->school_id_number);
        $this->assertNotNull($user->school_id_document_path);
        Storage::disk('local')->assertExists($user->school_id_document_path);
    }

    public function test_the_id_document_is_stored_privately_not_publicly(): void
    {
        $this->post(route('register'), $this->payload());

        $path = User::where('email', 'juan@example.com')->value('school_id_document_path');

        $this->assertStringStartsWith('school-ids/', $path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_registration_is_rejected_without_a_school_id(): void
    {
        $this->post(route('register'), $this->payload(['school_id_document' => null]))
            ->assertSessionHasErrors('school_id_document');

        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
    }

    /** A PHP payload renamed to .jpg must not pass — the check is on real content. */
    public function test_a_disguised_php_file_is_rejected(): void
    {
        $disguised = UploadedFile::fake()->createWithContent('id.jpg', '<?php echo "pwned"; ?>');

        $this->post(route('register'), $this->payload(['school_id_document' => $disguised]))
            ->assertSessionHasErrors('school_id_document');

        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
    }

    public function test_an_oversized_upload_is_rejected(): void
    {
        $this->post(route('register'), $this->payload([
            'school_id_document' => UploadedFile::fake()->image('id.jpg', 800, 600)->size(9000),
        ]))->assertSessionHasErrors('school_id_document');
    }

    public function test_an_unreadably_small_image_is_rejected(): void
    {
        $this->post(route('register'), $this->payload([
            'school_id_document' => UploadedFile::fake()->image('id.jpg', 50, 50)->size(100),
        ]))->assertSessionHasErrors('school_id_document');
    }

    public function test_the_registration_is_written_to_the_audit_trail(): void
    {
        $this->post(route('register'), $this->payload());

        $this->assertDatabaseHas('audit_logs', ['action' => 'registration_submitted']);
    }

    /*
    |--------------------------------------------------------------------------
    | Serving the document
    |--------------------------------------------------------------------------
    */

    private function applicant(?School $school = null): User
    {
        $this->post(route('register'), $this->payload([
            'school_id' => ($school ?? $this->school)->id,
        ]));

        return User::where('email', 'juan@example.com')->firstOrFail();
    }

    public function test_an_admin_of_the_same_school_can_view_the_document(): void
    {
        $applicant = $this->applicant();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true, 'school_id' => $this->school->id]);

        $this->actingAs($admin)
            ->get(route('admin.registrations.school-id', $applicant))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_an_admin_of_another_school_cannot_view_the_document(): void
    {
        $applicant = $this->applicant();
        $otherSchool = School::factory()->create(['is_active' => true]);
        $outsider = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true, 'school_id' => $otherSchool->id]);

        $this->actingAs($outsider)
            ->get(route('admin.registrations.school-id', $applicant))
            ->assertForbidden();
    }

    public function test_a_teacher_cannot_view_anyone_s_id_document(): void
    {
        $applicant = $this->applicant();
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER, 'is_active' => true, 'school_id' => $this->school->id]);

        $this->actingAs($teacher)
            ->get(route('admin.registrations.school-id', $applicant))
            ->assertForbidden();
    }

    public function test_a_guest_cannot_view_the_document(): void
    {
        // Registering logs the applicant straight in, so drop that session
        // before checking what an anonymous visitor can reach.
        $applicant = $this->applicant();
        auth()->logout();

        $this->get(route('admin.registrations.school-id', $applicant))->assertRedirect(route('login'));
    }

    public function test_viewing_a_document_is_audited(): void
    {
        $applicant = $this->applicant();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true, 'school_id' => $this->school->id]);

        $this->actingAs($admin)->get(route('admin.registrations.school-id', $applicant))->assertOk();

        $this->assertDatabaseHas('audit_logs', ['action' => 'school_id_document_viewed']);
    }

    /*
    |--------------------------------------------------------------------------
    | Approval
    |--------------------------------------------------------------------------
    */

    public function test_approving_records_who_verified_the_id_and_when(): void
    {
        $applicant = $this->applicant();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true, 'school_id' => $this->school->id]);

        $this->actingAs($admin)->post(route('admin.registrations.approve', $applicant))->assertRedirect();

        $applicant->refresh();
        $this->assertTrue($applicant->hasVerifiedSchoolId());
        $this->assertSame($admin->id, $applicant->school_id_verified_by);
        $this->assertSame(User::STATUS_APPROVED, $applicant->status);
    }

    public function test_an_admin_cannot_approve_an_applicant_from_another_school(): void
    {
        $applicant = $this->applicant();
        $otherSchool = School::factory()->create(['is_active' => true]);
        $outsider = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true, 'school_id' => $otherSchool->id]);

        $this->actingAs($outsider)
            ->post(route('admin.registrations.approve', $applicant))
            ->assertForbidden();

        $this->assertSame(User::STATUS_PENDING, $applicant->refresh()->status);
    }
}
