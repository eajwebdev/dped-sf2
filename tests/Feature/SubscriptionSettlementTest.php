<?php

namespace Tests\Feature;

use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * A teacher who has actually paid must end up subscribed — whether or not the
 * webhook ever arrives. It cannot arrive at all in local development, and in
 * production it can be delayed or dropped, so the return page reconciles
 * against PayMongo directly.
 */
class SubscriptionSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Without a secret key the gateway reads as unconfigured and no lookup
        // is ever attempted, so every settlement assertion would pass vacuously.
        config(['services.paymongo.secret_key' => 'sk_test_fake']);
    }

    private function teacher(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'trial_ends_at' => now()->subDay(), // trial just lapsed: this is why they are paying
        ]);
    }

    private function pendingPayment(User $user, array $attributes = []): SubscriptionPayment
    {
        return SubscriptionPayment::create($attributes + [
            'user_id' => $user->id,
            'provider' => 'paymongo',
            'provider_reference' => 'cs_test_123',
            'plan' => SubscriptionPlans::PROFESSIONAL,
            'kind' => SubscriptionPayment::KIND_PURCHASE,
            'months' => 3,
            'amount' => 80700,
            'discount_percent' => 0,
            'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);
    }

    /** A checkout session PayMongo reports as paid. */
    private function fakePaidSession(): void
    {
        Http::fake(['*/checkout_sessions/*' => Http::response([
            'data' => ['attributes' => [
                'payments' => [['attributes' => ['status' => 'paid']]],
            ]],
        ])]);
    }

    public function test_the_return_page_activates_the_subscription_without_any_webhook(): void
    {
        $user = $this->teacher();
        $payment = $this->pendingPayment($user);
        $this->fakePaidSession();

        $this->assertFalse($user->isSubscribed());

        $this->actingAs($user)->get(route('subscribe.success'))
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $payment->refresh();

        $this->assertSame(SubscriptionPayment::STATUS_PAID, $payment->status);
        $this->assertTrue($user->isSubscribed(), 'A paid teacher must be subscribed.');
        $this->assertSame(SubscriptionPlans::PROFESSIONAL, $user->subscription_plan);
        $this->assertSame(3, (int) now()->startOfDay()->diffInMonths($user->subscribed_until));
        $this->assertNotNull($payment->paid_at);
    }

    public function test_a_settled_payment_regains_access_to_the_whole_app(): void
    {
        $user = $this->teacher();
        $this->pendingPayment($user);
        $this->fakePaidSession();

        // Locked out before paying.
        $this->actingAs($user)->get(route('reports.sf5.index'))
            ->assertRedirect(route('subscribe.show'));

        $this->actingAs($user)->get(route('subscribe.success'));

        // Fully restored after.
        foreach (['teacher.dashboard', 'reports.sf3.index', 'reports.sf5.index', 'insights.index'] as $route) {
            $this->actingAs($user->fresh())->get(route($route))->assertOk();
        }
    }

    public function test_an_unpaid_session_grants_nothing(): void
    {
        $user = $this->teacher();
        $payment = $this->pendingPayment($user);

        // Visiting the success URL directly, without having paid.
        Http::fake(['*/checkout_sessions/*' => Http::response([
            'data' => ['attributes' => [
                'payments' => [],
                'payment_intent' => ['attributes' => ['status' => 'awaiting_payment_method']],
            ]],
        ])]);

        $this->actingAs($user)->get(route('subscribe.success'));

        $this->assertSame(SubscriptionPayment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertFalse($user->fresh()->isSubscribed(), 'An unpaid session must never grant access.');
    }

    public function test_the_return_page_and_the_webhook_never_double_credit(): void
    {
        $user = $this->teacher();
        $payment = $this->pendingPayment($user);
        $this->fakePaidSession();

        // Return page settles it first.
        $this->actingAs($user)->get(route('subscribe.success'));
        $until = $user->fresh()->subscribed_until;

        // The webhook then arrives for the same payment.
        $this->postJson(route('subscription.webhook'), [
            'data' => ['attributes' => [
                'type' => 'checkout_session.payment.paid',
                'data' => ['attributes' => ['reference_number' => (string) $payment->id]],
            ]],
        ])->assertOk();

        $this->assertEquals(
            $until->toDateString(),
            $user->fresh()->subscribed_until->toDateString(),
            'A single payment must extend the subscription exactly once.'
        );
        $this->assertSame(1, SubscriptionPayment::where('status', SubscriptionPayment::STATUS_PAID)->count());
    }

    public function test_the_webhook_still_works_on_its_own(): void
    {
        $user = $this->teacher();
        $payment = $this->pendingPayment($user);

        $this->postJson(route('subscription.webhook'), [
            'data' => ['attributes' => [
                'type' => 'checkout_session.payment.paid',
                'data' => ['attributes' => ['reference_number' => (string) $payment->id]],
            ]],
        ])->assertOk();

        $this->assertSame(SubscriptionPayment::STATUS_PAID, $payment->fresh()->status);
        $this->assertTrue($user->fresh()->isSubscribed());
    }

    public function test_starting_a_new_checkout_never_double_charges_an_already_paid_one(): void
    {
        $user = $this->teacher();
        $paid = $this->pendingPayment($user);
        $this->fakePaidSession();

        // The teacher saw no confirmation and clicks Subscribe again.
        $this->actingAs($user)
            ->post(route('subscribe.checkout'), ['plan' => SubscriptionPlans::PROFESSIONAL, 'months' => 3])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $paid->refresh();
        $this->assertSame(SubscriptionPayment::STATUS_PAID, $paid->status);
        $this->assertTrue($user->fresh()->isSubscribed());

        // Crucially: no second charge was opened.
        $this->assertSame(1, SubscriptionPayment::where('user_id', $user->id)->count());
    }

    public function test_a_genuinely_abandoned_checkout_is_closed_and_a_new_one_opens(): void
    {
        $user = $this->teacher();
        $abandoned = $this->pendingPayment($user);

        Http::fake([
            // The old session was never paid...
            '*/checkout_sessions/cs_test_123' => Http::response([
                'data' => ['attributes' => ['payments' => [], 'payment_intent' => ['attributes' => ['status' => 'awaiting_payment_method']]]],
            ]),
            // ...so a fresh checkout is created.
            '*/merchants/capabilities/payment_methods' => Http::response(['gcash', 'card']),
            '*/checkout_sessions' => Http::response([
                'data' => ['id' => 'cs_new_456', 'attributes' => ['checkout_url' => 'https://pay.test/new']],
            ]),
        ]);

        $this->actingAs($user)
            ->post(route('subscribe.checkout'), ['plan' => SubscriptionPlans::PROFESSIONAL, 'months' => 3])
            ->assertRedirect('https://pay.test/new');

        $this->assertSame(SubscriptionPayment::STATUS_CANCELLED, $abandoned->fresh()->status);
        $this->assertSame(2, SubscriptionPayment::where('user_id', $user->id)->count());
    }

    public function test_an_unreachable_gateway_leaves_open_payments_untouched(): void
    {
        $user = $this->teacher();
        $payment = $this->pendingPayment($user);

        // Lookup fails: assuming "unpaid" here is exactly what would double-charge.
        Http::fake([
            '*/checkout_sessions/cs_test_123' => Http::response(null, 500),
            '*/merchants/capabilities/payment_methods' => Http::response(['gcash']),
            '*/checkout_sessions' => Http::response([
                'data' => ['id' => 'cs_new_789', 'attributes' => ['checkout_url' => 'https://pay.test/new']],
            ]),
        ]);

        $this->actingAs($user)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::PROFESSIONAL, 'months' => 3,
        ]);

        $this->assertSame(
            SubscriptionPayment::STATUS_PENDING,
            $payment->fresh()->status,
            'An unverifiable payment must stay pending, never be written off as abandoned.'
        );
    }

    public function test_an_upgrade_moves_the_tier_without_extending_the_term(): void
    {
        $user = $this->teacher([
            'subscription_plan' => SubscriptionPlans::STARTER,
            'subscribed_until' => now()->addMonths(3),
        ]);
        $endsAt = $user->subscribed_until->toDateString();

        $this->pendingPayment($user, [
            'kind' => SubscriptionPayment::KIND_UPGRADE,
            'previous_plan' => SubscriptionPlans::STARTER,
        ]);
        $this->fakePaidSession();

        $this->actingAs($user)->get(route('subscribe.success'));

        $user->refresh();
        $this->assertSame(SubscriptionPlans::PROFESSIONAL, $user->subscription_plan);
        $this->assertSame($endsAt, $user->subscribed_until->toDateString(), 'An upgrade must not add free months.');
    }
}
