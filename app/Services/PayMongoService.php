<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper over the PayMongo Checkout Sessions API. Keys live in
 * config/services.php (env placeholders); when unset, callers should treat the
 * gateway as unconfigured rather than crash.
 */
class PayMongoService
{
    private const BASE_URL = 'https://api.paymongo.com/v1';

    public function isConfigured(): bool
    {
        return ! empty(Setting::paymongoSecretKey());
    }

    /** Monthly price in centavos. */
    /** What the subscriber is charged: the admin-set price, less any discount. */
    public function price(): int
    {
        return Setting::effectivePriceCentavos();
    }

    /** List price before discount, for showing a struck-through "was" amount. */
    public function listPrice(): int
    {
        return Setting::priceCentavos();
    }

    public function discountPercent(): int
    {
        return Setting::discountPercent();
    }

    /**
     * Create a hosted checkout session for one month of subscription.
     *
     * @return array{id: string, url: string}
     */
    public function createCheckoutSession(User $user, string $reference): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('PayMongo is not configured. Set the secret key under Admin → Settings → Payments.');
        }

        $response = Http::withBasicAuth(Setting::paymongoSecretKey(), '')
            ->acceptJson()
            ->post(self::BASE_URL.'/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'line_items' => [[
                            'currency' => 'PHP',
                            'amount' => $this->price(),
                            'name' => config('app.name').' — Monthly Subscription',
                            'quantity' => 1,
                        ]],
                        'payment_method_types' => ['card', 'gcash', 'paymaya'],
                        'description' => 'Monthly teacher subscription',
                        'reference_number' => $reference,
                        'success_url' => route('subscribe.success'),
                        'cancel_url' => route('subscribe.cancel'),
                        'billing' => [
                            'name' => $user->name,
                            'email' => $user->email,
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('PayMongo checkout failed: '.$response->body());
        }

        return [
            'id' => $response->json('data.id'),
            'url' => $response->json('data.attributes.checkout_url'),
        ];
    }

    /**
     * Verify a webhook payload against the Paymongo-Signature header. Returns
     * true when no webhook secret is configured (dev/sandbox convenience).
     *
     * Header format: "t=<timestamp>,te=<test sig>,li=<live sig>".
     */
    public function verifyWebhookSignature(string $payload, ?string $signatureHeader): bool
    {
        $secret = Setting::paymongoWebhookSecret();
        if (empty($secret)) {
            return true;
        }

        if (empty($signatureHeader)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            [$key, $value] = array_pad(explode('=', $segment, 2), 2, '');
            $parts[trim($key)] = trim($value);
        }

        $timestamp = $parts['t'] ?? null;
        $provided = $parts['li'] ?? $parts['te'] ?? null;
        if (! $timestamp || ! $provided) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return hash_equals($expected, $provided);
    }
}
