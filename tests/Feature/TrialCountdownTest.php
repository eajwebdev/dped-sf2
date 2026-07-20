<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The 14-day trial has to be visible on every page while it runs, and has to
 * shut the app down the moment it lapses.
 */
class TrialCountdownTest extends TestCase
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

    public function test_the_countdown_shows_on_every_teacher_page(): void
    {
        $trial = $this->teacher(['trial_ends_at' => now()->addDays(9)]);

        foreach (['teacher.dashboard', 'reports.sf1.index', 'reports.sf3.index', 'reports.sf5.index', 'insights.index'] as $route) {
            $this->actingAs($trial)->get(route($route))
                ->assertOk()
                ->assertSee('9 days')
                ->assertSee('free trial', false)
                ->assertSee(route('subscribe.show'), false);
        }
    }

    public function test_the_day_count_counts_down_and_reads_naturally_at_the_end(): void
    {
        // Freeze at midday so relative offsets never straddle a day boundary:
        // addHours(3) must still read as "today" no matter when (or in which
        // timezone) the suite runs.
        $this->travelTo(now()->startOfDay()->addHours(12));

        $this->actingAs($this->teacher(['trial_ends_at' => now()->addDays(14)]))
            ->get(route('teacher.dashboard'))->assertSee('14 days');

        // The count sits in its own <span>, so assert the singular noun survives.
        $this->actingAs($this->teacher(['trial_ends_at' => now()->addDays(1)]))
            ->get(route('teacher.dashboard'))
            ->assertSee('>1</span> day', false)
            ->assertDontSee('day s');

        // Final day: no "0 days left", an explicit call to act instead.
        $this->actingAs($this->teacher(['trial_ends_at' => now()->addHours(3)]))
            ->get(route('teacher.dashboard'))
            ->assertSee('ends today', false)
            ->assertDontSee('0 days');
    }

    public function test_an_expired_trial_loses_access_to_every_gated_page(): void
    {
        $expired = $this->teacher(['trial_ends_at' => now()->subDay()]);

        $this->assertSame('expired', $expired->subscriptionState());
        $this->assertFalse($expired->hasActiveAccess());

        foreach ([
            'teacher.dashboard', 'attendance.index', 'schedule.index',
            'teacher.students.index', 'teacher.subjects.index',
            'reports.sf1.index', 'reports.sf2.index', 'reports.sf3.index',
            'reports.sf5.index', 'insights.index',
        ] as $route) {
            $this->actingAs($expired)->get(route($route))
                ->assertRedirect(route('subscribe.show'));
        }
    }

    public function test_an_expired_teacher_can_still_reach_the_subscribe_page(): void
    {
        $expired = $this->teacher(['trial_ends_at' => now()->subDay()]);

        // The paywall is useless if the lapsed user cannot get to it.
        $this->actingAs($expired)->get(route('subscribe.show'))->assertOk();
    }

    public function test_subscribers_and_comped_accounts_see_no_countdown(): void
    {
        $subscribed = $this->teacher(['subscribed_until' => now()->addMonths(3)]);
        $comped = $this->teacher(['free_access' => true]);
        $managed = $this->teacher();

        foreach ([$subscribed, $comped, $managed] as $user) {
            $this->actingAs($user)->get(route('teacher.dashboard'))
                ->assertOk()
                ->assertDontSee('free trial');
        }
    }
}
