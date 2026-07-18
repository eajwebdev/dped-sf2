<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\PayMongoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The subscription webhook is unauthenticated and CSRF-exempt by necessity —
 * the signature is the only thing standing between a stranger and free paid
 * access. These tests pin that down.
 */
class SubscriptionWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsk_test_secret';

    private function sign(string $payload, string $secret = self::SECRET, string $key = 'te'): string
    {
        $t = (string) time();

        return "t={$t},{$key}=".hash_hmac('sha256', $t.'.'.$payload, $secret);
    }

    public function test_a_valid_signature_is_accepted(): void
    {
        Setting::put('paymongo_webhook_secret', self::SECRET);
        $payload = '{"data":{"attributes":{"type":"payment.paid"}}}';

        $this->assertTrue(
            app(PayMongoService::class)->verifyWebhookSignature($payload, $this->sign($payload))
        );
    }

    public function test_a_forged_signature_is_rejected(): void
    {
        Setting::put('paymongo_webhook_secret', self::SECRET);
        $payload = '{"data":{"attributes":{"type":"payment.paid"}}}';

        $this->assertFalse(
            app(PayMongoService::class)->verifyWebhookSignature($payload, $this->sign($payload, 'wrong_secret'))
        );
    }

    public function test_a_tampered_payload_is_rejected(): void
    {
        Setting::put('paymongo_webhook_secret', self::SECRET);
        $signature = $this->sign('{"amount":100}');

        $this->assertFalse(
            app(PayMongoService::class)->verifyWebhookSignature('{"amount":999999}', $signature)
        );
    }

    public function test_a_missing_signature_header_is_rejected(): void
    {
        Setting::put('paymongo_webhook_secret', self::SECRET);

        $this->assertFalse(
            app(PayMongoService::class)->verifyWebhookSignature('{}', null)
        );
    }

    /**
     * The important one: with no secret configured the check must fail closed
     * outside local/testing, or anyone can POST themselves a paid subscription.
     */
    public function test_an_unconfigured_secret_fails_closed_in_production(): void
    {
        Setting::put('paymongo_webhook_secret', '');
        app()->detectEnvironment(fn () => 'production');

        $this->assertFalse(
            app(PayMongoService::class)->verifyWebhookSignature('{}', null)
        );
    }

    public function test_the_unsigned_webhook_route_is_rejected_in_production(): void
    {
        Setting::put('paymongo_webhook_secret', '');
        app()->detectEnvironment(fn () => 'production');

        $this->postJson(route('subscription.webhook'), [
            'data' => ['attributes' => ['type' => 'checkout_session.payment.paid']],
        ])->assertStatus(401);
    }
}
