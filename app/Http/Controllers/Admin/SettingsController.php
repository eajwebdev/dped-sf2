<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Support\SubscriptionPlans;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Owner settings, organized by module: Pricing (what teachers pay) and
 * Payments (PayMongo gateway credentials). Each module posts with its own
 * `module` field so saving one never clobbers the other.
 */
class SettingsController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly \App\Services\PayMongoService $paymongo,
    ) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        // One editable row per tier: list price, its own promo, and what that
        // actually charges today — the same numbers the landing page shows.
        $tiers = [];
        foreach (SubscriptionPlans::all() as $key => $meta) {
            $tiers[$key] = [
                'key' => $key,
                'name' => $meta['name'],
                'tagline' => $meta['tagline'],
                'price' => SubscriptionPlans::monthlyPrice($key) / 100,
                'discount' => SubscriptionPlans::promoPercent($key),
                'effective' => SubscriptionPlans::effectiveMonthlyPrice($key) / 100,
            ];
        }

        return view('admin.settings.index', [
            'tiers' => $tiers,
            // Secrets never travel back to the browser — only a masked hint.
            // The public key is publishable by definition, so it's shown in full.
            'secretHint' => $this->mask(Setting::paymongoSecretKey()),
            'publicFull' => Setting::paymongoPublicKey(),
            'webhookHint' => $this->mask(Setting::paymongoWebhookSecret()),

            // Everything that must be true before real money can be taken.
            'readiness' => $this->paymongo->readiness(),
            'liveMode' => $this->paymongo->isLiveMode(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        return match ($request->input('module')) {
            'payments' => $this->updatePayments($request),
            default => $this->updatePricing($request),
        };
    }

    /**
     * Per-tier pricing: each plan carries its own list price and its own promo
     * discount, and both take effect immediately on the landing page and at
     * checkout. Only tiers actually present in the request are touched.
     */
    private function updatePricing(Request $request): RedirectResponse
    {
        $keys = SubscriptionPlans::keys();

        $data = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*' => ['required', 'numeric', 'min:1', 'max:100000'],
            'discounts' => ['required', 'array'],
            'discounts.*' => ['required', 'integer', 'min:0', 'max:100'],
        ], [], [
            'prices.starter' => 'Starter price',
            'prices.professional' => 'Professional price',
            'prices.enterprise' => 'Enterprise price',
            'discounts.starter' => 'Starter discount',
            'discounts.professional' => 'Professional discount',
            'discounts.enterprise' => 'Enterprise discount',
        ]);

        $changes = [];

        foreach ($keys as $plan) {
            if (! isset($data['prices'][$plan], $data['discounts'][$plan])) {
                continue;
            }

            $price = (int) round(((float) $data['prices'][$plan]) * 100);
            $discount = (int) $data['discounts'][$plan];

            $wasPrice = SubscriptionPlans::monthlyPrice($plan);
            $wasDiscount = SubscriptionPlans::promoPercent($plan);

            Setting::put(SubscriptionPlans::settingKey($plan), $price);
            Setting::put(SubscriptionPlans::discountSettingKey($plan), $discount);

            if ($price !== $wasPrice || $discount !== $wasDiscount) {
                $changes[] = sprintf('%s ₱%s → ₱%s (%d%% off → %d%% off)',
                    SubscriptionPlans::find($plan)['name'],
                    number_format($wasPrice / 100, 2), number_format($price / 100, 2),
                    $wasDiscount, $discount,
                );
            }
        }

        if (! $changes) {
            return redirect()->route('admin.settings.index')->with('success', 'Pricing unchanged.');
        }

        $this->audit->log('settings_updated', null, 'Plan pricing updated: '.implode('; ', $changes));

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pricing updated — the landing page and checkout now show the new rates.');
    }

    private function updatePayments(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'paymongo_secret_key' => ['nullable', 'string', 'max:200'],
            'paymongo_public_key' => ['nullable', 'string', 'max:200'],
            'paymongo_webhook_secret' => ['nullable', 'string', 'max:200'],
        ]);

        // Blank fields mean "keep what's there" — masked hints are shown, so an
        // untouched form must never wipe working credentials.
        $updated = [];
        foreach ([
            'paymongo_secret_key' => Setting::PAYMONGO_SECRET,
            'paymongo_public_key' => Setting::PAYMONGO_PUBLIC,
            'paymongo_webhook_secret' => Setting::PAYMONGO_WEBHOOK,
        ] as $field => $key) {
            if (filled($data[$field] ?? null)) {
                Setting::put($key, trim($data[$field]));
                $updated[] = str_replace('paymongo_', '', $field);
            }
        }

        if (! $updated) {
            return redirect()->route('admin.settings.index')
                ->with('success', 'Nothing changed — leave a field blank to keep its current value.');
        }

        // Never log the values themselves.
        $this->audit->log('settings_updated', null, 'PayMongo credentials updated: '.implode(', ', $updated));

        return redirect()->route('admin.settings.index')
            ->with('success', 'PayMongo settings saved ('.implode(', ', $updated).').');
    }

    /** "sk_test_a1b2…9z8y" → enough to recognize, never enough to use. */
    private function mask(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return strlen($value) <= 8
            ? substr($value, 0, 2).'…'
            : substr($value, 0, 8).'…'.substr($value, -4);
    }
}
