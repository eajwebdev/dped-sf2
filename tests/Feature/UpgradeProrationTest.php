<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Mid-term upgrades are prorated by day, in line with how subscription
 * products are normally billed. The property that matters commercially:
 * upgrading later never costs more than having started on the higher tier.
 */
class UpgradeProrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::put(SubscriptionPlans::settingKey('starter'), 19900);
        Setting::put(SubscriptionPlans::settingKey('professional'), 26900);
        Setting::put(SubscriptionPlans::discountSettingKey('starter'), 0);
        Setting::put(SubscriptionPlans::discountSettingKey('professional'), 0);
    }

    /** A teacher $daysIn into a 3-month Starter term. */
    private function starterSubscriber(int $daysIn): User
    {
        $start = Carbon::today()->subDays($daysIn);
        $end = $start->copy()->addMonthsNoOverflow(3);

        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'subscription_plan' => SubscriptionPlans::STARTER,
            'subscribed_until' => $end,
        ]);

        SubscriptionPayment::create([
            'user_id' => $user->id,
            'provider' => 'paymongo',
            'provider_reference' => 'cs_term',
            'plan' => SubscriptionPlans::STARTER,
            'kind' => SubscriptionPayment::KIND_PURCHASE,
            'months' => 3,
            'amount' => SubscriptionPlans::quote(SubscriptionPlans::STARTER, 3)['total'],
            'discount_percent' => 6,
            'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PAID,
            'paid_at' => $start,
            'period_start' => $start,
            'period_end' => $end,
        ]);

        return $user;
    }

    private function quoteFor(User $user): array
    {
        return SubscriptionPlans::upgradeQuote(
            SubscriptionPlans::STARTER,
            SubscriptionPlans::PROFESSIONAL,
            $user->subscriptionTermMonths(),
            $user->subscriptionRemainingRatio(),
        );
    }

    public function test_upgrading_never_costs_more_than_starting_on_the_higher_tier(): void
    {
        // The commercial promise. If this fails, upgrading punishes the customer.
        $starterTerm = SubscriptionPlans::quote(SubscriptionPlans::STARTER, 3)['total'];
        $proTerm = SubscriptionPlans::quote(SubscriptionPlans::PROFESSIONAL, 3)['total'];

        foreach ([0, 7, 30, 60, 80] as $daysIn) {
            $user = $this->starterSubscriber($daysIn);
            $paidInTotal = $starterTerm + $this->quoteFor($user)['total'];

            $this->assertLessThanOrEqual($proTerm, $paidInTotal,
                "Upgrading after {$daysIn} days cost more than buying Professional outright.");
        }
    }

    public function test_upgrading_on_day_one_equals_the_full_price_gap(): void
    {
        $user = $this->starterSubscriber(0);

        $gap = SubscriptionPlans::quote(SubscriptionPlans::PROFESSIONAL, 3)['total']
             - SubscriptionPlans::quote(SubscriptionPlans::STARTER, 3)['total'];

        $this->assertSame($gap, $this->quoteFor($user)['total']);
        // ₱807 − ₱597, both less the 6% advance discount = ₱197.40
        $this->assertSame(19740, $this->quoteFor($user)['total']);
    }

    public function test_the_charge_shrinks_as_the_term_runs_down(): void
    {
        $previous = PHP_INT_MAX;

        foreach ([0, 7, 30, 60, 80] as $daysIn) {
            $total = $this->quoteFor($this->starterSubscriber($daysIn))['total'];

            $this->assertLessThan($previous, $total, "Cost did not fall by day {$daysIn}.");
            $previous = $total;
        }
    }

    public function test_a_week_in_is_prorated_not_rounded_up_to_a_whole_month(): void
    {
        $total = $this->quoteFor($this->starterSubscriber(7))['total'];

        // Old behaviour billed 3 whole months of list difference: ₱210.00.
        $this->assertNotSame(21000, $total, 'Part-months must not be rounded up.');
        $this->assertGreaterThan(18000, $total);
        $this->assertLessThan(19740, $total);
    }

    public function test_the_end_date_never_moves_and_the_tier_changes(): void
    {
        $user = $this->starterSubscriber(7);
        $endsAt = $user->subscribed_until->toDateString();

        $payment = SubscriptionPayment::create([
            'user_id' => $user->id,
            'provider' => 'paymongo',
            'provider_reference' => 'cs_upgrade',
            'plan' => SubscriptionPlans::PROFESSIONAL,
            'kind' => SubscriptionPayment::KIND_UPGRADE,
            'previous_plan' => SubscriptionPlans::STARTER,
            'months' => 3,
            'amount' => $this->quoteFor($user)['total'],
            'discount_percent' => 0,
            'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $this->postJson(route('subscription.webhook'), [
            'data' => ['attributes' => [
                'type' => 'checkout_session.payment.paid',
                'data' => ['attributes' => ['reference_number' => (string) $payment->id]],
            ]],
        ])->assertOk();

        $user->refresh();
        $this->assertSame(SubscriptionPlans::PROFESSIONAL, $user->subscription_plan);
        $this->assertSame($endsAt, $user->subscribed_until->toDateString());
        $this->assertTrue($user->hasModule('sf3'), 'The new tier unlocks immediately.');
    }

    public function test_a_promo_on_the_target_tier_reaches_the_upgrade_price(): void
    {
        $user = $this->starterSubscriber(0);
        $before = $this->quoteFor($user)['total'];

        Setting::put(SubscriptionPlans::discountSettingKey('professional'), 25);

        $this->assertLessThan($before, $this->quoteFor($user)['total'],
            'An admin discount on the target tier must reduce the upgrade price too.');
    }

    public function test_an_admin_granted_subscription_without_a_purchase_still_quotes(): void
    {
        // No payment row explains the period; the quote must not divide by zero.
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'subscription_plan' => SubscriptionPlans::STARTER,
            'subscribed_until' => Carbon::today()->addMonths(2),
        ]);

        $quote = $this->quoteFor($user);

        $this->assertGreaterThan(0, $quote['total']);
        $this->assertLessThanOrEqual(1.0, $quote['remaining_ratio']);
    }
}
