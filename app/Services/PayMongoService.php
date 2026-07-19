<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Support\Facades\Cache;
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
            // A hung gateway must not hold a request open indefinitely, and a
            // transient network blip should not read as "checkout failed".
            // Only the create call is retried, and PayMongo treats a repeat
            // create as a new session, so retries stay safe: nothing is charged
            // until the teacher acts on the hosted page.
            ->timeout(20)
            ->retry(2, 400, throw: false)
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
     * Fetch a checkout session back from PayMongo.
     *
     * This is what lets the app confirm a payment without waiting for the
     * webhook — essential locally (PayMongo cannot reach a localhost URL, so
     * the webhook never arrives at all) and a safety net in production, where
     * a webhook can be delayed, misconfigured or dropped. Returns null rather
     * than throwing: a failed lookup must never break the return page.
     *
     * @return array<string, mixed>|null
     */
    public function retrieveCheckoutSession(string $sessionId): ?array
    {
        if (! $this->isConfigured() || $sessionId === '') {
            return null;
        }

        try {
            $response = Http::withBasicAuth(Setting::paymongoSecretKey(), '')
                ->acceptJson()
                ->timeout(15)
                ->get(self::BASE_URL.'/checkout_sessions/'.$sessionId);
        } catch (\Throwable $e) {
            return null;
        }

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Whether a retrieved checkout session has actually been paid.
     *
     * PayMongo reports this in two places depending on the method used, so
     * both are checked: a settled entry in `payments[]`, or a succeeded
     * payment intent. Anything else (awaiting_payment_method, processing) is
     * deliberately not treated as paid.
     *
     * @param  array<string, mixed>|null  $session
     */
    public function checkoutSessionIsPaid(?array $session): bool
    {
        if (! $session) {
            return false;
        }

        foreach ((array) data_get($session, 'data.attributes.payments', []) as $payment) {
            if (data_get($payment, 'attributes.status') === 'paid') {
                return true;
            }
        }

        return data_get($session, 'data.attributes.payment_intent.attributes.status') === 'succeeded';
    }

    /**
     * Total actually settled on a checkout session, in centavos.
     *
     * Access is granted on the strength of this number rather than on what the
     * app hoped would be charged, so a session that completed for less than
     * the quoted amount cannot buy a full subscription. Null means the amount
     * could not be determined and no amount check is possible.
     *
     * @param  array<string, mixed>|null  $session
     */
    public function paidAmount(?array $session): ?int
    {
        if (! $session) {
            return null;
        }

        $total = 0;
        $found = false;

        foreach ((array) data_get($session, 'data.attributes.payments', []) as $payment) {
            $amount = data_get($payment, 'attributes.amount');

            // Only a present, numeric amount counts. Treating a missing field as
            // zero would read a genuine payment as a zero-peso underpayment and
            // lock out a customer who actually paid.
            if (data_get($payment, 'attributes.status') === 'paid' && is_numeric($amount)) {
                $total += (int) $amount;
                $found = true;
            }
        }

        if ($found) {
            return $total;
        }

        $intent = data_get($session, 'data.attributes.payment_intent.attributes');
        $amount = data_get($intent, 'amount');

        return data_get($intent, 'status') === 'succeeded' && is_numeric($amount) ? (int) $amount : null;
    }

    /** Whether the configured secret key is a live (real money) key. */
    public function isLiveMode(): bool
    {
        return str_starts_with((string) Setting::paymongoSecretKey(), 'sk_live');
    }

    /**
     * Everything that must be true before real payments are taken, as
     * [check => [ok, detail]] so the admin screen can show what is missing.
     *
     * @return array<string, array{ok: bool, detail: string}>
     */
    public function readiness(): array
    {
        $secret = Setting::paymongoSecretKey();
        $webhook = Setting::paymongoWebhookSecret();
        $url = (string) config('app.url');

        return [
            'Secret key' => [
                'ok' => filled($secret),
                'detail' => filled($secret)
                    ? ($this->isLiveMode() ? 'Live key configured.' : 'Test key — payments are sandboxed, no real money moves.')
                    : 'Not set. Checkout is disabled until a secret key is saved.',
            ],
            'Webhook secret' => [
                'ok' => filled($webhook),
                'detail' => filled($webhook)
                    ? 'Set — webhook signatures are verified.'
                    : 'Not set. In production every webhook is rejected, so payments rely solely on the return page.',
            ],
            'Public site URL' => [
                'ok' => str_starts_with($url, 'https://'),
                'detail' => str_starts_with($url, 'https://')
                    ? $url
                    : $url.' — PayMongo cannot deliver webhooks here. Set APP_URL to your public HTTPS domain.',
            ],
            'Webhook endpoint' => [
                'ok' => str_starts_with($url, 'https://'),
                'detail' => rtrim($url, '/').'/subscription/webhook — register this in the PayMongo dashboard '
                    .'for checkout_session.payment.paid.',
            ],
            'Cache & sessions' => $this->cacheReadiness(),
        ];
    }

    /**
     * Cache and session storage, which decide whether reads are served from
     * memory or turned back into database round trips.
     *
     * @return array{ok: bool, detail: string}
     */
    private function cacheReadiness(): array
    {
        $cache = (string) config('cache.default');
        $session = (string) config('session.driver');
        $slow = array_values(array_filter([
            $cache === 'database' ? 'cache' : null,
            $session === 'database' ? 'sessions' : null,
        ]));

        if ($slow !== []) {
            return [
                'ok' => false,
                'detail' => sprintf('%s still stored in the database — every cached read becomes a query. '
                    .'Use redis (multi-server) or file (single server).', ucfirst(implode(' and ', $slow))),
            ];
        }

        return [
            'ok' => true,
            'detail' => sprintf('cache: %s, sessions: %s.%s', $cache, $session,
                in_array('file', [$cache, $session], true)
                    ? ' File storage is per-server — move to redis if you run more than one web server.'
                    : ''),
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
        // Cached briefly: this sits in the checkout path, where it is an extra
        // round trip and an extra thing that can fail between the teacher
        // clicking Subscribe and reaching the payment page. Enabling a new
        // method in PayMongo shows up within the minute.
        $methods = Cache::remember('paymongo:payment_methods', now()->addMinutes(60), function () {
            $response = Http::withBasicAuth(Setting::paymongoSecretKey(), '')
                ->acceptJson()
                ->timeout(15)
                ->retry(2, 300, throw: false)
                ->get(self::BASE_URL.'/merchants/capabilities/payment_methods');

            return $response->successful() ? $response->json() : null;
        });

        if (! is_array($methods) || $methods === []) {
            Cache::forget('paymongo:payment_methods');

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
