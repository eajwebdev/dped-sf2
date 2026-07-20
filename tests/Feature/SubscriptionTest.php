<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SubscriptionPayment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    public function test_admin_pages_render(): void
    {
        $admin = $this->admin();
        School::factory()->create();
        User::factory()->create(['role' => User::ROLE_TEACHER, 'status' => User::STATUS_PENDING]);

        $this->actingAs($admin)->get(route('admin.schools.index'))->assertOk()->assertSee('Schools');
        $this->actingAs($admin)->get(route('admin.registrations.index'))->assertOk()->assertSee('Registrations');
    }

    public function test_admin_can_add_a_school(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.schools.store'), [
                'school_id' => '123456',
                'name' => 'Dela Paz Central School',
                'education_level' => 'jhs_shs',
                'is_active' => 1,
            ])->assertRedirect(route('admin.schools.index'));

        $this->assertDatabaseHas('schools', [
            'school_id' => '123456',
            'name' => 'Dela Paz Central School',
            'education_level' => 'jhs_shs',
        ]);
    }

    public function test_approving_a_registration_starts_the_trial_and_creates_a_teacher(): void
    {
        $school = School::factory()->create();
        $pending = User::factory()->create([
            'name' => 'Maria Santos',
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_PENDING,
            'school_id' => $school->id,
            'contact_number' => '09171234567',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.registrations.approve', $pending))
            ->assertRedirect();

        $pending->refresh();
        $this->assertSame(User::STATUS_APPROVED, $pending->status);
        $this->assertNotNull($pending->trial_ends_at);
        $this->assertTrue($pending->onTrial());
        $this->assertDatabaseHas('teachers', ['user_id' => $pending->id, 'last_name' => 'Santos']);
    }

    public function test_a_trial_teacher_can_use_the_app(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'trial_ends_at' => Carbon::now()->addDays(5),
        ]);

        $this->actingAs($teacher)->get(route('teacher.dashboard'))->assertOk();
    }

    public function test_an_expired_teacher_is_pushed_to_subscribe(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'trial_ends_at' => Carbon::now()->subDay(),
            'subscribed_until' => null,
        ]);

        $this->actingAs($teacher)->get(route('teacher.dashboard'))
            ->assertRedirect(route('subscribe.show'));
    }

    public function test_a_managed_teacher_without_billing_is_not_gated(): void
    {
        // Admin-provisioned teacher: approved, but never enrolled in billing.
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'trial_ends_at' => null,
            'subscribed_until' => null,
        ]);

        $this->assertTrue($teacher->hasActiveAccess());
        $this->actingAs($teacher)->get(route('teacher.dashboard'))->assertOk();
    }

    public function test_webhook_marks_payment_paid_and_extends_subscription(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'trial_ends_at' => Carbon::now()->subDay(),
        ]);

        $payment = SubscriptionPayment::create([
            'user_id' => $teacher->id,
            'provider' => 'paymongo',
            'provider_reference' => 'cs_test_123',
            'amount' => 29900,
            'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $payload = [
            'data' => ['attributes' => [
                'type' => 'checkout_session.payment.paid',
                'data' => ['id' => 'cs_test_123', 'attributes' => ['reference_number' => (string) $payment->id]],
            ]],
        ];

        $this->postJson(route('subscription.webhook'), $payload)->assertOk();

        $payment->refresh();
        $teacher->refresh();

        $this->assertSame(SubscriptionPayment::STATUS_PAID, $payment->status);
        $this->assertNotNull($teacher->subscribed_until);
        $this->assertTrue($teacher->isSubscribed());
        $this->assertTrue($teacher->hasActiveAccess());
    }
}
