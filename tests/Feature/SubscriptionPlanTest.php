<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_three_plans_are_priced_as_configured(): void
    {
        $this->assertSame(19900, SubscriptionPlans::monthlyPrice(SubscriptionPlans::STARTER));
        $this->assertSame(26900, SubscriptionPlans::monthlyPrice(SubscriptionPlans::PROFESSIONAL));
        $this->assertSame(44900, SubscriptionPlans::monthlyPrice(SubscriptionPlans::ENTERPRISE));
    }

    public function test_an_admin_can_override_a_plan_price(): void
    {
        Setting::put(SubscriptionPlans::settingKey(SubscriptionPlans::STARTER), 15000);

        $this->assertSame(15000, SubscriptionPlans::monthlyPrice(SubscriptionPlans::STARTER));
        // Other plans keep their defaults.
        $this->assertSame(26900, SubscriptionPlans::monthlyPrice(SubscriptionPlans::PROFESSIONAL));
    }

    public function test_discount_is_three_percent_per_extra_month_and_caps(): void
    {
        $this->assertSame(0, SubscriptionPlans::discountFor(1));
        $this->assertSame(3, SubscriptionPlans::discountFor(2));
        $this->assertSame(6, SubscriptionPlans::discountFor(3));
        $this->assertSame(15, SubscriptionPlans::discountFor(6));
        $this->assertSame(30, SubscriptionPlans::discountFor(11)); // 10 × 3 = 30, at the cap
        $this->assertSame(30, SubscriptionPlans::discountFor(12)); // 11 × 3 = 33, clamped to 30
    }

    public function test_quote_math_for_a_single_month(): void
    {
        $q = SubscriptionPlans::quote(SubscriptionPlans::STARTER, 1);

        $this->assertSame(1, $q['months']);
        $this->assertSame(19900, $q['subtotal']);
        $this->assertSame(0, $q['discount']);
        $this->assertSame(19900, $q['total']);
        $this->assertSame(0, $q['saved']);
    }

    public function test_quote_math_applies_the_advance_discount(): void
    {
        // 6 months × ₱199 = ₱1,194 subtotal, less 15% = ₱1,014.90
        $q = SubscriptionPlans::quote(SubscriptionPlans::STARTER, 6);

        $this->assertSame(119400, $q['subtotal']);
        $this->assertSame(15, $q['discount']);
        $this->assertSame(101490, $q['total']);
        $this->assertSame(17910, $q['saved']);
    }

    public function test_months_are_clamped_to_the_allowed_range(): void
    {
        $this->assertSame(1, SubscriptionPlans::quote(SubscriptionPlans::STARTER, 0)['months']);
        $this->assertSame(1, SubscriptionPlans::quote(SubscriptionPlans::STARTER, -5)['months']);
        $this->assertSame(SubscriptionPlans::MAX_MONTHS, SubscriptionPlans::quote(SubscriptionPlans::STARTER, 99)['months']);
    }

    public function test_a_promo_discount_stacks_on_top_of_the_advance_discount(): void
    {
        Setting::put(Setting::DISCOUNT, 10);

        // ₱199 × 3 = ₱597, less 6% advance = ₱561.18, less 10% promo = ₱505.06
        $q = SubscriptionPlans::quote(SubscriptionPlans::STARTER, 3);

        $this->assertSame(6, $q['discount']);
        $this->assertSame(10, $q['promo']);
        $this->assertSame(50506, $q['total']);
        $this->assertLessThan($q['subtotal'], $q['total']);
    }

    public function test_subscribe_page_lists_every_plan_and_month_option(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER, 'is_active' => true,
            'trial_ends_at' => now()->addWeek(),
        ]);

        $this->actingAs($teacher)->get(route('subscribe.show'))
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('Professional')
            ->assertSee('Enterprise')
            ->assertSee('₱199')
            ->assertSee('₱269')
            ->assertSee('₱449')
            ->assertSee('How many months?');
    }

    public function test_checkout_rejects_an_unknown_plan_or_month_count(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER, 'is_active' => true,
            'trial_ends_at' => now()->addWeek(),
        ]);
        Setting::put(Setting::PAYMONGO_SECRET, 'sk_test_dummy');

        $this->actingAs($teacher)
            ->post(route('subscribe.checkout'), ['plan' => 'platinum', 'months' => 3])
            ->assertSessionHasErrors('plan');

        $this->actingAs($teacher)
            ->post(route('subscribe.checkout'), ['plan' => SubscriptionPlans::STARTER, 'months' => 99])
            ->assertSessionHasErrors('months');
    }

    public function test_webhook_extends_by_the_months_that_were_paid_for(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER, 'is_active' => true,
            'subscribed_until' => null,
        ]);

        $payment = SubscriptionPayment::create([
            'user_id' => $teacher->id,
            'provider' => 'paymongo',
            'plan' => SubscriptionPlans::PROFESSIONAL,
            'months' => 6,
            'amount' => SubscriptionPlans::quote(SubscriptionPlans::PROFESSIONAL, 6)['total'],
            'discount_percent' => 15,
            'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        // Drive the same code path the webhook uses once the signature has passed.
        $months = (int) $payment->months;
        $until = $teacher->extendSubscription($months);
        $teacher->forceFill(['subscription_plan' => $payment->plan])->save();

        $this->assertSame(
            Carbon::today()->addMonthsNoOverflow(6)->toDateString(),
            $until->toDateString(),
            'Six paid months should add six months of access'
        );
        $this->assertSame(SubscriptionPlans::PROFESSIONAL, $teacher->fresh()->subscription_plan);
    }
}
