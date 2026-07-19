<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Every transaction that actually started has to be traceable, whatever its
 * outcome — paid, declined, refunded, abandoned or never reaching the gateway.
 * Only a teacher who never attempted payment leaves no trail.
 */
class SubscriptionAuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsk_test_secret';

    private function teacher(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'trial_ends_at' => now()->addDays(3),
        ]);
    }

    private function configureGateway(): void
    {
        Setting::put('paymongo_secret_key', 'sk_test_key');
        Setting::put('paymongo_webhook_secret', self::SECRET);
    }

    private function signedWebhook(array $event): array
    {
        $payload = json_encode($event);
        $t = (string) time();

        return [$payload, "t={$t},te=".hash_hmac('sha256', $t.'.'.$payload, self::SECRET)];
    }

    private function postWebhook(array $event)
    {
        [$payload, $signature] = $this->signedWebhook($event);

        return $this->call('POST', route('subscription.webhook'), [], [], [],
            ['HTTP_PAYMONGO_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $payload);
    }

    public function test_a_teacher_who_never_paid_leaves_no_transaction_trail(): void
    {
        $teacher = $this->teacher();

        $this->actingAs($teacher)->get(route('subscribe.show'))->assertOk();

        $this->assertDatabaseMissing('audit_logs', ['action' => 'subscription_checkout_started']);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'subscription_payment_paid']);
    }

    public function test_starting_a_checkout_is_audited(): void
    {
        $this->configureGateway();
        Http::fake(['*' => Http::response(['data' => ['id' => 'cs_123', 'attributes' => ['checkout_url' => 'https://pay.test/cs_123']]], 200)]);

        $teacher = $this->teacher();

        $this->actingAs($teacher)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::STARTER,
            'months' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'subscription_checkout_started',
            'user_id' => $teacher->id,
        ]);
    }

    public function test_a_gateway_error_while_starting_checkout_is_audited(): void
    {
        $this->configureGateway();
        Http::fake(['*' => Http::response(['errors' => [['detail' => 'boom']]], 500)]);

        $teacher = $this->teacher();

        $this->actingAs($teacher)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::STARTER,
            'months' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', ['action' => 'subscription_checkout_failed']);
        $this->assertDatabaseHas('subscription_payments', ['status' => SubscriptionPayment::STATUS_FAILED]);
    }

    public function test_an_unconfigured_gateway_attempt_is_audited(): void
    {
        $teacher = $this->teacher();

        $this->actingAs($teacher)->post(route('subscribe.checkout'), [
            'plan' => SubscriptionPlans::STARTER,
            'months' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', ['action' => 'subscription_checkout_unavailable']);
    }

    public function test_a_successful_payment_is_audited_against_the_paying_teacher(): void
    {
        $this->configureGateway();
        $teacher = $this->teacher();

        $payment = SubscriptionPayment::create([
            'user_id' => $teacher->id, 'provider' => 'paymongo', 'plan' => SubscriptionPlans::STARTER,
            'months' => 1, 'amount' => 19900, 'discount_percent' => 0, 'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $this->postWebhook([
            'data' => ['attributes' => [
                'type' => 'checkout_session.payment.paid',
                'data' => ['id' => 'cs_1', 'attributes' => ['reference_number' => (string) $payment->id]],
            ]],
        ])->assertOk();

        // Nobody is logged in on a webhook, so attribution has to be explicit.
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'subscription_payment_paid',
            'user_id' => $teacher->id,
        ]);
        $this->assertSame(SubscriptionPayment::STATUS_PAID, $payment->refresh()->status);
    }

    public function test_a_declined_payment_is_audited_and_grants_no_access(): void
    {
        $this->configureGateway();
        $teacher = $this->teacher();

        $payment = SubscriptionPayment::create([
            'user_id' => $teacher->id, 'provider' => 'paymongo', 'plan' => SubscriptionPlans::STARTER,
            'months' => 1, 'amount' => 19900, 'discount_percent' => 0, 'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $this->postWebhook([
            'data' => ['attributes' => [
                'type' => 'payment.failed',
                'data' => ['id' => 'pay_1', 'attributes' => ['reference_number' => (string) $payment->id]],
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'subscription_payment_failed',
            'user_id' => $teacher->id,
        ]);
        $this->assertSame(SubscriptionPayment::STATUS_FAILED, $payment->refresh()->status);
        $this->assertNull($teacher->refresh()->subscribed_until);
    }

    public function test_cancelling_at_the_gateway_is_audited(): void
    {
        $teacher = $this->teacher();

        SubscriptionPayment::create([
            'user_id' => $teacher->id, 'provider' => 'paymongo', 'plan' => SubscriptionPlans::STARTER,
            'months' => 1, 'amount' => 19900, 'discount_percent' => 0, 'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $this->actingAs($teacher)->get(route('subscribe.cancel'))->assertRedirect();

        $this->assertDatabaseHas('audit_logs', ['action' => 'subscription_checkout_cancelled']);
        $this->assertDatabaseHas('subscription_payments', ['status' => SubscriptionPayment::STATUS_CANCELLED]);
    }
}
