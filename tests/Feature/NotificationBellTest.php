<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    private function notices(User $user): array
    {
        return app(NotificationService::class)->for($user);
    }

    public function test_a_subscription_far_from_expiry_raises_nothing(): void
    {
        $teacher = $this->teacher(['subscribed_until' => Carbon::today()->addMonths(6)]);

        $this->assertSame([], $this->notices($teacher));
    }

    public function test_a_subscription_inside_the_warning_window_is_flagged(): void
    {
        $teacher = $this->teacher(['subscribed_until' => Carbon::today()->addDays(10)]);

        $notices = $this->notices($teacher);

        $this->assertCount(1, $notices);
        $this->assertSame('warning', $notices[0]['level']);
        $this->assertStringContainsString('10 days', $notices[0]['title']);
    }

    public function test_an_imminent_expiry_escalates_to_danger(): void
    {
        $teacher = $this->teacher(['subscribed_until' => Carbon::today()->addDays(2)]);

        $this->assertSame('danger', $this->notices($teacher)[0]['level']);
    }

    public function test_expiring_today_reads_as_today(): void
    {
        $teacher = $this->teacher(['subscribed_until' => Carbon::today()]);

        $this->assertStringContainsString('ends today', $this->notices($teacher)[0]['title']);
    }

    public function test_a_lapsed_subscription_is_reported_as_ended(): void
    {
        $teacher = $this->teacher([
            'subscribed_until' => Carbon::today()->subDay(),
            'trial_ends_at' => Carbon::now()->subMonth(),
        ]);

        $notices = $this->notices($teacher);

        $this->assertSame('danger', $notices[0]['level']);
        $this->assertStringContainsString('ended', $notices[0]['title']);
    }

    public function test_a_trial_nearing_its_end_is_flagged(): void
    {
        $teacher = $this->teacher(['trial_ends_at' => Carbon::now()->addDays(5)]);

        $notices = $this->notices($teacher);

        $this->assertCount(1, $notices);
        $this->assertStringContainsString('free trial', $notices[0]['title']);
    }

    public function test_a_pending_teacher_is_told_their_id_is_under_review(): void
    {
        $teacher = $this->teacher(['status' => User::STATUS_PENDING]);

        $notices = $this->notices($teacher);

        $this->assertCount(1, $notices);
        $this->assertSame('awaiting-approval', $notices[0]['id']);
    }

    public function test_comped_accounts_get_no_expiry_nagging(): void
    {
        $teacher = $this->teacher([
            'free_access' => true,
            'subscribed_until' => Carbon::today()->addDay(),
        ]);

        $this->assertSame([], $this->notices($teacher));
    }

    public function test_admins_have_no_subscription_notices(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->assertSame([], $this->notices($admin));
    }

    public function test_the_bell_renders_the_expiry_warning_in_the_header(): void
    {
        $teacher = $this->teacher(['subscribed_until' => Carbon::today()->addDays(5)]);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('ends in 5 days');
    }
}
