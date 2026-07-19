<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\PayMongoService;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Guards for taking real money: the amount has to match, the scheduled sweep
 * has to recover payments nobody confirmed, and unsigned webhooks must not be
 * honoured in production.
 */
class SubscriptionProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.paymongo.secret_key' => 'sk_test_fake']);
    }

    private function teacher(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'trial_ends_at' => now()->subDay(),
        ]);
    }

    private function pendingPayment(User $user, int $amount = 80700): SubscriptionPayment
    {
        return SubscriptionPayment::create([
            'user_id' => $user->id,
            'provider' => 'paymongo',
            'provider_reference' => 'cs_test_123',
            'plan' => SubscriptionPlans::PROFESSIONAL,
            'kind' => SubscriptionPayment::KIND_PURCHASE,
            'months' => 3,
            'amount' => $amount,
            'discount_percent' => 0,
            'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);
    }

    private function fakeSessionPaying(int $amount): void
    {
        Http::fake(['*/checkout_sessions/*' => Http::response([
            'data' => ['attributes' => [
                'payments' => [['attributes' => ['status' => 'paid', 'amount' => $amount]]],
            ]],
        ])]);
    }

    public function test_a_short_payment_does_not_buy_a_subscription(): void
    {
        $user = $this->teacher();
        $payment = $this->pendingPayment($user, 80700);

        // Settled for ₱1.00 against an ₱807.00 quote.
        $this->fakeSessionPaying(100);

        $this->actingAs($user)->get(route('subscribe.success'))
            ->assertRedirect(route('subscribe.show'))
            ->assertSessionHas('error');

        $this->assertSame(SubscriptionPayment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertFalse($user->fresh()->isSubscribed(), 'An underpayment must never grant access.');
    }

    public function test_a_paid_session_without_an_amount_is_still_honoured(): void
    {
        $user = $this->teacher();
        $payment = $this->pendingPayment($user);

        // Amount absent from the payload: unknowable, so the check is skipped
        // rather than read as a zero-peso payment.
        Http::fake(['*/checkout_sessions/*' => Http::response([
            'data' => ['attributes' => ['payments' => [['attributes' => ['status' => 'paid']]]]],
        ])]);

        $this->assertNull(app(PayMongoService::class)->paidAmount([
            'data' => ['attributes' => ['payments' => [['attributes' => ['status' => 'paid']]]]],
        ]));

        $this->actingAs($user)->get(route('subscribe.success'))->assertRedirect(route('dashboard'));

        $this->assertSame(SubscriptionPayment::STATUS_PAID, $payment->fresh()->status);
        $this->assertTrue($user->fresh()->isSubscribed());
    }

    public function test_the_exact_amount_is_accepted(): void
    {
        $user = $this->teacher();
        $this->pendingPayment($user, 80700);
        $this->fakeSessionPaying(80700);

        $this->actingAs($user)->get(route('subscribe.success'))->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->isSubscribed());
    }

    public function test_the_scheduled_sweep_recovers_a_payment_nobody_confirmed(): void
    {
        $user = $this->teacher();
        $payment = $this->pendingPayment($user);
        $this->fakeSessionPaying(80700);

        // The teacher paid, then closed the tab. No return, no webhook.
        $this->assertFalse($user->isSubscribed());

        $this->artisan('subscriptions:reconcile')
            ->expectsOutputToContain('settled')
            ->assertSuccessful();

        $this->assertSame(SubscriptionPayment::STATUS_PAID, $payment->fresh()->status);
        $this->assertTrue($user->fresh()->isSubscribed(), 'The sweep is the only path that does not need the customer to return.');
    }

    public function test_the_sweep_leaves_unpaid_and_unreachable_checkouts_pending(): void
    {
        $user = $this->teacher();
        $unpaid = $this->pendingPayment($user);

        Http::fake(['*/checkout_sessions/*' => Http::response([
            'data' => ['attributes' => ['payments' => [], 'payment_intent' => ['attributes' => ['status' => 'awaiting_payment_method']]]],
        ])]);

        $this->artisan('subscriptions:reconcile')->assertSuccessful();
        $this->assertSame(SubscriptionPayment::STATUS_PENDING, $unpaid->fresh()->status);

        // A gateway that cannot be reached must also leave the row alone.
        Http::fake(['*/checkout_sessions/*' => Http::response(null, 500)]);

        $this->artisan('subscriptions:reconcile')->assertSuccessful();
        $this->assertSame(SubscriptionPayment::STATUS_PENDING, $unpaid->fresh()->status);
        $this->assertFalse($user->fresh()->isSubscribed());
    }

    public function test_the_sweep_is_idempotent(): void
    {
        $user = $this->teacher();
        $this->pendingPayment($user);
        $this->fakeSessionPaying(80700);

        $this->artisan('subscriptions:reconcile')->assertSuccessful();
        $until = $user->fresh()->subscribed_until;

        $this->artisan('subscriptions:reconcile')->assertSuccessful();

        $this->assertEquals($until->toDateString(), $user->fresh()->subscribed_until->toDateString(),
            'Re-running the sweep must never extend a subscription again.');
    }

    public function test_an_unsigned_webhook_is_rejected_when_a_secret_is_configured(): void
    {
        $user = $this->teacher();
        $payment = $this->pendingPayment($user);

        Setting::put(Setting::PAYMONGO_WEBHOOK, 'whsk_secret');

        // No Paymongo-Signature header: forging access must not be possible.
        $this->postJson(route('subscription.webhook'), [
            'data' => ['attributes' => [
                'type' => 'checkout_session.payment.paid',
                'data' => ['attributes' => ['reference_number' => (string) $payment->id]],
            ]],
        ])->assertStatus(401);

        $this->assertSame(SubscriptionPayment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertFalse($user->fresh()->isSubscribed());
    }

    public function test_readiness_reports_what_is_missing(): void
    {
        config(['app.url' => 'http://localhost']);
        Setting::put(Setting::PAYMONGO_WEBHOOK, '');

        $readiness = app(PayMongoService::class)->readiness();

        $this->assertTrue($readiness['Secret key']['ok']);
        $this->assertFalse($readiness['Webhook secret']['ok'], 'A missing webhook secret must be flagged.');
        $this->assertFalse($readiness['Public site URL']['ok'], 'A non-HTTPS APP_URL cannot receive webhooks.');

        config(['app.url' => 'https://sf2.example.ph']);
        Setting::put(Setting::PAYMONGO_WEBHOOK, 'whsk_live');

        $ready = app(PayMongoService::class)->readiness();
        $this->assertTrue(collect($ready)->every(fn ($c) => $c['ok']));
    }

    public function test_the_admin_settings_page_shows_the_readiness_checklist(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
        ]);

        config(['app.url' => 'http://localhost']);

        $this->actingAs($admin)->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Payment readiness')
            ->assertSee('Webhook secret')
            ->assertSee('subscriptions:reconcile');
    }

    public function test_live_mode_is_called_out_on_the_admin_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
        ]);

        Setting::put(Setting::PAYMONGO_SECRET, 'sk_live_realmoney');

        $this->actingAs($admin)->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('charge real money');
    }

    public function test_a_test_key_is_not_reported_as_live_mode(): void
    {
        $this->assertFalse(app(PayMongoService::class)->isLiveMode());

        Setting::put(Setting::PAYMONGO_SECRET, 'sk_live_realmoney');
        $this->assertTrue(app(PayMongoService::class)->isLiveMode());
    }
}
