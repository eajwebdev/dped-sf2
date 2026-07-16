<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Owner settings, organized by module: Pricing (what teachers pay) and
 * Payments (PayMongo gateway credentials). Each module posts with its own
 * `module` field so saving one never clobbers the other.
 */
class SettingsController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('admin.settings.index', [
            'price' => Setting::priceCentavos() / 100,
            'discount' => Setting::discountPercent(),
            'effective' => Setting::effectivePriceCentavos() / 100,
            // Secrets never travel back to the browser — only a masked hint.
            // The public key is publishable by definition, so it's shown in full.
            'secretHint' => $this->mask(Setting::paymongoSecretKey()),
            'publicFull' => Setting::paymongoPublicKey(),
            'webhookHint' => $this->mask(Setting::paymongoWebhookSecret()),
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

    private function updatePricing(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Entered in pesos; stored in centavos to match PayMongo.
            'price' => ['required', 'numeric', 'min:1', 'max:100000'],
            'discount' => ['required', 'integer', 'min:0', 'max:100'],
        ], [], ['price' => 'monthly price', 'discount' => 'discount']);

        Setting::put(Setting::PRICE, (int) round($data['price'] * 100));
        Setting::put(Setting::DISCOUNT, $data['discount']);

        $this->audit->log('settings_updated', null,
            "Subscription price set to ₱{$data['price']} with {$data['discount']}% discount");

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pricing updated. New subscriptions are charged ₱'
                .number_format(Setting::effectivePriceCentavos() / 100, 2).'.');
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
