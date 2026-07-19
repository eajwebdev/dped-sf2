<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** The subscribe page has three quite different jobs depending on account state. */
class SubscribePageStatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The purchase controls only render when a gateway is configured.
        \App\Models\Setting::put('paymongo_secret_key', 'sk_test_key');
    }

    private function teacher(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    public function test_a_trial_user_sees_the_normal_purchase_screen(): void
    {
        $user = $this->teacher(['trial_ends_at' => now()->addDays(5)]);

        $this->actingAs($user)->get(route('subscribe.show'))
            ->assertOk()
            ->assertSee('How many months?')
            // No server-rendered upgrade banner or prorated card price.
            ->assertDontSee("You're on Starter until", false)
            ->assertDontSee('/mo ×', false);
    }

    public function test_a_mid_term_subscriber_sees_the_upgrade_screen_with_the_prorated_price(): void
    {
        $user = $this->teacher([
            'subscription_plan' => SubscriptionPlans::STARTER,
            'subscribed_until' => Carbon::today()->addMonthsNoOverflow(3),
        ]);

        $response = $this->actingAs($user)->get(route('subscribe.show'))->assertOk();

        $response->assertSee("You're on Starter until", false);
        $response->assertSee('3 months left');
        // (449 − 199) × 3 = 750, and (269 − 199) × 3 = 210.
        $response->assertSee('Upgrade for ₱750', false);
        $response->assertSee('Upgrade for ₱210', false);
    }

    public function test_a_subscriber_on_the_top_tier_mid_term_is_told_there_is_nothing_to_buy(): void
    {
        $user = $this->teacher([
            'subscription_plan' => SubscriptionPlans::ENTERPRISE,
            'subscribed_until' => Carbon::today()->addMonthsNoOverflow(5),
        ]);

        $this->actingAs($user)->get(route('subscribe.show'))
            ->assertOk()
            ->assertSee("You're on our highest plan", false);
    }

    public function test_a_subscriber_near_expiry_can_renew(): void
    {
        $user = $this->teacher([
            'subscription_plan' => SubscriptionPlans::ENTERPRISE,
            'subscribed_until' => Carbon::today()->addDays(5),
        ]);

        $this->actingAs($user)->get(route('subscribe.show'))
            ->assertOk()
            ->assertSee('How many months?')
            ->assertDontSee("You're on our highest plan", false);
    }
}
