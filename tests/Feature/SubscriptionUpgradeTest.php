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

    /** The worked example: ₱199 with 3 months left → ₱449 costs (449−199)×3 = ₱750. */
    public function test_an_upgrade_costs_the_difference_times_the_months_remaining(): void
    {
        $quote = SubscriptionPlans::upgradeQuote(
            SubscriptionPlans::STARTER, SubscriptionPlans::ENTERPRISE, 3
        );

        $this->assertSame(25000, $quote['monthly_difference']);  // ₱449 − ₱199
        $this->assertSame(75000, $quote['total']);               // × 3 months
        $this->assertSame(3, $quote['months']);
    }

    public function test_upgrading_one_tier_is_priced_off_the_current_plan_not_the_cheapest(): void
    {
        $quote = SubscriptionPlans::upgradeQuote(
            SubscriptionPlans::PROFESSIONAL, SubscriptionPlans::ENTERPRISE, 2
        );

        $this->assertSame(18000, $quote['monthly_difference']);  // ₱449 − ₱269
        $this->assertSame(36000, $quote['total']);
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
        $this->assertSame(75000, $payment->amount);
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
        $this->assertSame(50000, SubscriptionPayment::firstOrFail()->amount);
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
