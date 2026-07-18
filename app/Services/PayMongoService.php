<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Support\SubscriptionPlans;
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
     * Create a hosted checkout session for a plan bought for one or more months.
     *
     * The whole purchase is charged as a single line item, because PayMongo
     * multiplies amount x quantity and the advance-payment discount would be
     * lost if the months were sent as the quantity.
     *
     * @param  array{months:int, monthly:int, subtotal:int, discount:int, promo:int, total:int, saved:int}  $quote
     * @return array{id: string, url: string}
     */
    public function createCheckoutSession(User $user, string $reference, string $plan = SubscriptionPlans::STARTER, ?array $quote = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('PayMongo is not configured. Set the secret key under Admin → Settings → Payments.');
        }

        $quote ??= SubscriptionPlans::quote($plan, 1);
        $meta = SubscriptionPlans::find($plan);
        $months = $quote['months'];
        $label = $months === 1 ? '1 month' : $months.' months';

        $response = Http::withBasicAuth(Setting::paymongoSecretKey(), '')
            ->acceptJson()
            ->post(self::BASE_URL.'/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'line_items' => [[
                            'currency' => 'PHP',
                            'amount' => $quote['total'],
                            'name' => config('app.name')." - {$meta['name']} Plan ({$label})",
                            'quantity' => 1,
                        ]],
                        'payment_method_types' => $this->availablePaymentMethods(),
                        'description' => "{$meta['name']} plan - {$label}"
                            .($quote['discount'] > 0 ? " - {$quote['discount']}% advance discount" : ''),
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
     * The payment methods actually activated on the merchant account. A
     * hardcoded list gets intersected with these by PayMongo, and any mismatch
     * renders a checkout with "No payment methods are available".
     *
     * @return array<string>
     */
    public function availablePaymentMethods(): array
    {
        $methods = Http::withBasicAuth(Setting::paymongoSecretKey(), '')
            ->acceptJson()
            ->get(self::BASE_URL.'/merchants/capabilities/payment_methods')
            ->json();

        if (! is_array($methods) || $methods === []) {
            throw new RuntimeException(
                'Your PayMongo account has no activated payment methods. '
                .'Enable them in the PayMongo dashboard under Settings → Payment methods.'
            );
        }

        return array_values($methods);
    }

    /**
     * Verify a webhook payload against the Paymongo-Signature header.
     *
     * An unsigned webhook grants paid access to whoever calls it, so a missing
     * secret fails CLOSED everywhere except local/testing, where sandbox events
     * are replayed by hand. Configure the secret before taking real payments.
     *
     * Header format: "t=<timestamp>,te=<test sig>,li=<live sig>".
     */
    public function verifyWebhookSignature(string $payload, ?string $signatureHeader): bool
    {
        $secret = Setting::paymongoWebhookSecret();
        if (empty($secret)) {
            return app()->environment(['local', 'testing']);
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
