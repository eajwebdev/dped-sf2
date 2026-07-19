<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * An account comped by the admin (free_access) has unlimited access, so the
 * subscribe page must never present it as expired or push it to pay.
 */
class CompedAccountStatusTest extends TestCase
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

    public function test_a_comped_account_is_shown_as_unlimited_not_expired(): void
    {
        // free_access on top of a lapsed trial: the comp must win.
        $comped = $this->teacher([
            'free_access' => true,
            'trial_ends_at' => now()->subMonth(),
        ]);

        $this->assertSame('free', $comped->subscriptionState());

        $this->actingAs($comped)->get(route('subscribe.show'))
            ->assertOk()
            ->assertSee('Unlimited access')
            ->assertSee('nothing to pay')
            ->assertDontSee('Expired')
            ->assertDontSee('Subscribe to continue using');
    }

    public function test_a_comped_account_keeps_working_across_the_app(): void
    {
        $comped = $this->teacher([
            'free_access' => true,
            'trial_ends_at' => now()->subMonth(),
        ]);

        foreach (['teacher.dashboard', 'reports.sf3.index', 'reports.sf5.index', 'insights.index'] as $route) {
            $this->actingAs($comped)->get(route($route))->assertOk();
        }
    }

    public function test_a_genuinely_expired_account_still_reads_as_expired(): void
    {
        $expired = $this->teacher(['trial_ends_at' => now()->subDay()]);

        $this->actingAs($expired)->get(route('subscribe.show'))
            ->assertOk()
            ->assertSee('Expired')
            ->assertDontSee('Unlimited access');
    }
}
