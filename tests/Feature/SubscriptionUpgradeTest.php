<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * An active subscriber has already paid for their term, so they cannot buy it
 * again — they can only move up a tier, topping up the price difference for
 * the months they have left, with the end date unchanged.
 */
class SubscriptionUpgradeTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsk_test_secret';

    private function subscriber(string $plan, int $monthsLeft): User
    {
        return User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'subscription_plan' => $plan,
            'subscribed_until' => Carbon::today()->addMonthsNoOverflow($monthsLeft),
        ]);
    }

    private function configureGateway(): void
    {
        Setting::put('paymongo_secret_key', 'sk_test_key');
        Setting::put('paymongo_webhook_secret', self::SECRET);
        Http::fake(['*' => Http::response(
            ['data' => ['id' => 'cs_1', 'attributes' => ['checkout_url' => 'https://pay.test/cs_1']]], 200
        )]);
    }

    private function payWebhook(SubscriptionPayment $payment)
    {
        $payload = json_encode(['data' => ['attributes' => [
            'type' => 'checkout_session.payment.paid',
            'data' => ['id' => 'cs_1', 'attributes' => ['reference_number' => (string) $payment->id]],
        ]]]);
        $t = (string) time();
        $signature = "t={$t},te=".hash_hmac('sha256', $t.'.'.$payload, self::SECRET);

        return $this->call('POST', route('subscription.webhook'), [], [], [],
            ['HTTP_PAYMONGO_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $payload);
    }

    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    */

    /**
     * The worked example, with a full term still unused: ₱199 → ₱449 over 3
     * months. Both sides carry the same 6% advance-payment discount, so the
     * top-up is (₱1,347 − ₱597) less 6% = ₱705 — not the ₱750 raw list
     * difference. Charging the list gap would make upgrading cost more than
     * buying Enterprise outright, which is the one thing proration exists to
     * prevent.
     */
    public function test_an_upgrade_costs_the_gap_between_the_two_full_terms(): void
    {
        $quote = SubscriptionPlans::upgradeQuote(
            SubscriptionPlans::STARTER, SubscriptionPlans::ENTERPRISE, 3, 1.0
        );

        $this->assertSame(70500, $quote['total']);
        $this->assertSame(23500, $quote['monthly_difference']);
        $this->assertSame(3, $quote['months']);

        // The property that matters: no penalty for having started smaller.
        $this->assertSame(
            SubscriptionPlans::quote(SubscriptionPlans::ENTERPRISE, 3)['total'],
            SubscriptionPlans::quote(SubscriptionPlans::STARTER, 3)['total'] + $quote['total'],
        );
    }

    public function test_upgrading_one_tier_is_priced_off_the_current_plan_not_the_cheapest(): void
    {
        $quote = SubscriptionPlans::upgradeQuote(
            SubscriptionPlans::PROFESSIONAL, SubscriptionPlans::ENTERPRISE, 2, 1.0
        );

        // (₱898 − ₱538) less the 3% two-month advance discount.
        $this->assertSame(34920, $quote['total']);
        $this->assertSame(17460, $quote['monthly_difference']);
    }

    public function test_only_the_unused_part_of_the_term_is_charged(): void
    {
        $full = SubscriptionPlans::upgradeQuote(
            SubscriptionPlans::STARTER, SubscriptionPlans::ENTERPRISE, 3, 1.0
        )['total'];

        $third = SubscriptionPlans::upgradeQuote(
            SubscriptionPlans::STARTER, SubscriptionPlans::ENTERPRISE, 3, 1 / 3
        )['total'];

        $this->assertSame((int) round($full / 3), $third);
    }

    public function test_remaining_months_round_a_part_month_up(): void
    {
        $user = $this->subscriber(SubscriptionPlans::STARTER, 3);
        $user->forceFill(['subscribed_until' => Carbon::today()->addMonthsNoOverflow(3)->addDays(4)])->save();

        $this->assertSame(4, $user->remainingSubscriptionMonths());
    }

    /*
    |--------------------------------------------------------------------------
    | What an active subscriber may do
    |--------------------------------------------------------------------------
    */

    public function test_an_active_subscriber_cannot_buy_the_same_plan_again(): void
    {
        $this->configureGateway();
        $user = $this->subscriber(SubscriptionPlans::STARTER, 3);

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::STARTER,
            'months' => 3,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('subscription_payments', 0);
    }

    public function test_an_active_subscriber_cannot_downgrade_mid_term(): void
    {
        $this->configureGateway();
        $user = $this->subscriber(SubscriptionPlans::ENTERPRISE, 3);

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::STARTER,
            'months' => 3,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('subscription_payments', 0);
    }

    public function test_an_active_subscriber_may_upgrade(): void
    {
        $this->configureGateway();
        $user = $this->subscriber(SubscriptionPlans::STARTER, 3);

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::ENTERPRISE,
            'months' => 12,   // ignored: an upgrade covers the months already paid for
        ])->assertRedirect();

        $payment = SubscriptionPayment::firstOrFail();

        $this->assertSame(SubscriptionPayment::KIND_UPGRADE, $payment->kind);
        $this->assertSame(SubscriptionPlans::STARTER, $payment->previous_plan);
        $this->assertSame(3, $payment->months);
        // Gap between the two 3-month terms, both after the 6% advance discount.
        $this->assertSame(70500, $payment->amount);
    }

    public function test_the_month_count_on_an_upgrade_cannot_be_inflated_from_the_form(): void
    {
        $this->configureGateway();
        $user = $this->subscriber(SubscriptionPlans::STARTER, 2);

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::ENTERPRISE,
            'months' => 12,
        ])->assertRedirect();

        // Two months left, so two months of difference — not twelve.
        // (₱898 − ₱398) less the 3% two-month advance discount = ₱485.
        $this->assertSame(48500, SubscriptionPayment::firstOrFail()->amount);
    }

    public function test_renewal_opens_up_near_the_end_of_the_term(): void
    {
        $this->configureGateway();
        $user = $this->subscriber(SubscriptionPlans::STARTER, 0);
        $user->forceFill(['subscribed_until' => Carbon::today()->addDays(5)])->save();

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::STARTER,
            'months' => 3,
        ])->assertRedirect();

        $payment = SubscriptionPayment::firstOrFail();
        $this->assertSame(SubscriptionPayment::KIND_PURCHASE, $payment->kind);
        $this->assertSame(3, $payment->months);
    }

    public function test_a_lapsed_subscriber_can_buy_any_plan_normally(): void
    {
        $this->configureGateway();
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER, 'is_active' => true, 'status' => User::STATUS_APPROVED,
            'subscription_plan' => SubscriptionPlans::ENTERPRISE,
            'subscribed_until' => Carbon::today()->subDay(),
        ]);

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::STARTER,
            'months' => 3,
        ])->assertRedirect();

        $this->assertSame(SubscriptionPayment::KIND_PURCHASE, SubscriptionPayment::firstOrFail()->kind);
    }

    /*
    |--------------------------------------------------------------------------
    | Applying a paid upgrade
    |--------------------------------------------------------------------------
    */

    public function test_a_paid_upgrade_changes_the_tier_without_moving_the_end_date(): void
    {
        $this->configureGateway();
        $user = $this->subscriber(SubscriptionPlans::STARTER, 3);
        $originalUntil = $user->subscribed_until->copy();

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::ENTERPRISE, 'months' => 3,
        ])->assertRedirect();

        $this->payWebhook(SubscriptionPayment::firstOrFail())->assertOk();

        $user->refresh();
        $this->assertSame(SubscriptionPlans::ENTERPRISE, $user->subscription_plan);
        $this->assertSame($originalUntil->toDateString(), $user->subscribed_until->toDateString());
    }

    public function test_a_paid_purchase_still_extends_the_end_date(): void
    {
        $this->configureGateway();
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER, 'is_active' => true, 'status' => User::STATUS_APPROVED,
            'subscribed_until' => null, 'trial_ends_at' => now()->addDay(),
        ]);

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::STARTER, 'months' => 3,
        ])->assertRedirect();

        $this->payWebhook(SubscriptionPayment::firstOrFail())->assertOk();

        $this->assertSame(
            Carbon::today()->addMonthsNoOverflow(3)->toDateString(),
            $user->refresh()->subscribed_until->toDateString()
        );
    }

    public function test_an_upgrade_is_audited_as_an_upgrade(): void
    {
        $this->configureGateway();
        $user = $this->subscriber(SubscriptionPlans::STARTER, 3);

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::ENTERPRISE, 'months' => 3,
        ])->assertRedirect();

        $this->payWebhook(SubscriptionPayment::firstOrFail())->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'subscription_payment_paid',
            'user_id' => $user->id,
        ]);
        $this->assertStringContainsString('Upgrade confirmed',
            \App\Models\AuditLog::where('action', 'subscription_payment_paid')->value('description'));
    }
}
