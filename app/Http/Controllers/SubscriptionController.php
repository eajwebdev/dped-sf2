<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\AuditLogger;
use App\Services\PayMongoService;
use App\Support\SubscriptionPlans;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly PayMongoService $paymongo,
        private readonly AuditLogger $audit,
    ) {}

    /** Status screen for pending / rejected registrations. */
    public function pending(Request $request)
    {
        $user = $request->user();

        // Already active? Nothing to wait for.
        if ($user->hasActiveAccess()) {
            return redirect()->route('dashboard');
        }

        return view('account.pending');
    }

    /** The subscribe / renew page. */
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        // Pre-compute a quote per plan per month count so the page can show a
        // live total without a round trip, and the server stays the authority.
        $quotes = [];
        foreach (SubscriptionPlans::keys() as $plan) {
            for ($m = 1; $m <= SubscriptionPlans::MAX_MONTHS; $m++) {
                $quotes[$plan][$m] = SubscriptionPlans::quote($plan, $m);
            }
        }

        return view('subscribe.show', [
            'plans' => SubscriptionPlans::all(),
            'quotes' => $quotes,
            'maxMonths' => SubscriptionPlans::MAX_MONTHS,
            'perMonthDiscount' => SubscriptionPlans::DISCOUNT_PER_EXTRA_MONTH,
            'maxDiscount' => SubscriptionPlans::MAX_DISCOUNT_PERCENT,
            'currentPlan' => $user->subscription_plan ?? SubscriptionPlans::STARTER,
            'configured' => $this->paymongo->isConfigured(),
        ]);
    }

    /** Start a PayMongo checkout for a plan bought for one or more months. */
    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $this->paymongo->isConfigured()) {
            $this->audit->log('subscription_checkout_unavailable', $user,
                'Checkout attempted while the payment gateway is unconfigured');

            return back()->with('error', 'Online payment is not available yet. Please contact your administrator.');
        }

        $validated = $request->validate([
            'plan' => ['required', Rule::in(SubscriptionPlans::keys())],
            'months' => ['required', 'integer', 'min:1', 'max:'.SubscriptionPlans::MAX_MONTHS],
        ]);

        // The quote is recomputed here so a tampered form cannot set the price.
        $plan = $validated['plan'];
        $quote = SubscriptionPlans::quote($plan, (int) $validated['months']);

        $payment = SubscriptionPayment::create([
            'user_id' => $user->id,
            'provider' => 'paymongo',
            'plan' => $plan,
            'months' => $quote['months'],
            'amount' => $quote['total'],
            'discount_percent' => $quote['discount'],
            'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $this->audit->log('subscription_checkout_started', $payment,
            sprintf('Checkout started: %s × %d month(s), ₱%s', $plan, $quote['months'], number_format($quote['total'] / 100, 2)),
            null,
            ['plan' => $plan, 'months' => $quote['months'], 'amount' => $quote['total'], 'discount_percent' => $quote['discount']],
        );

        try {
            $session = $this->paymongo->createCheckoutSession($user, (string) $payment->id, $plan, $quote);
        } catch (Throwable $e) {
            Log::error('PayMongo checkout failed', ['user' => $user->id, 'error' => $e->getMessage()]);
            $payment->update(['status' => SubscriptionPayment::STATUS_FAILED]);

            // A gateway error is exactly the kind of failure an admin needs to
            // see, so it is audited alongside successful transactions.
            $this->audit->log('subscription_checkout_failed', $payment,
                'Could not open a PayMongo checkout session',
                null,
                ['error' => substr($e->getMessage(), 0, 500)],
            );

            return back()->with('error', 'We could not start the payment. Please try again.');
        }

        $payment->update(['provider_reference' => $session['id']]);

        return redirect()->away($session['url']);
    }

    public function success(): RedirectResponse
    {
        // Access is granted by the webhook; this is just the friendly return page.
        return redirect()->route('dashboard')
            ->with('success', 'Thank you! Your payment is being confirmed — access unlocks within a minute.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        // Abandoning at the gateway is a real outcome for a transaction that was
        // actually started, so it belongs in the trail.
        $payment = SubscriptionPayment::where('user_id', $request->user()?->id)
            ->where('status', SubscriptionPayment::STATUS_PENDING)
            ->latest('id')
            ->first();

        if ($payment) {
            $payment->update(['status' => SubscriptionPayment::STATUS_CANCELLED]);
            $this->audit->log('subscription_checkout_cancelled', $payment,
                'Teacher cancelled the payment at the gateway');
        }

        return redirect()->route('subscribe.show')->with('error', 'Payment was cancelled.');
    }

    /**
     * PayMongo webhook. On a successful checkout payment, mark the ledger row
     * paid and extend the teacher's subscription by one month.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();

        if (! $this->paymongo->verifyWebhookSignature($payload, $request->header('Paymongo-Signature'))) {
            return response()->json(['ok' => false, 'message' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true) ?: [];
        $type = data_get($event, 'data.attributes.type');

        $paidTypes = ['checkout_session.payment.paid', 'payment.paid'];
        $failedTypes = ['payment.failed', 'checkout_session.payment.failed', 'payment.refunded'];

        if (! in_array($type, [...$paidTypes, ...$failedTypes], true)) {
            return response()->json(['ok' => true, 'ignored' => $type]);
        }

        $reference = data_get($event, 'data.attributes.data.attributes.reference_number');
        $payment = $reference
            ? SubscriptionPayment::find($reference)
            : SubscriptionPayment::where('provider_reference', data_get($event, 'data.attributes.data.id'))->first();

        if (! $payment) {
            return response()->json(['ok' => true, 'message' => 'No matching payment']);
        }

        // A declined card or a refund never grants access, but it is a real
        // transaction outcome and has to be traceable.
        if (in_array($type, $failedTypes, true)) {
            $payment->update(['status' => SubscriptionPayment::STATUS_FAILED, 'payload' => $event]);

            $this->audit->log('subscription_payment_failed', $payment,
                sprintf('Payment %s at the gateway (%s)', $type === 'payment.refunded' ? 'refunded' : 'declined', $type),
                null,
                ['type' => $type, 'plan' => $payment->plan, 'amount' => $payment->amount],
                $payment->user_id,
            );

            return response()->json(['ok' => true, 'recorded' => $type]);
        }

        // Idempotent: ignore replays of an already-processed payment.
        if ($payment->status === SubscriptionPayment::STATUS_PAID) {
            return response()->json(['ok' => true, 'message' => 'Already processed']);
        }

        $user = $payment->user;
        $months = max(1, (int) $payment->months);
        $newUntil = $user->extendSubscription($months);

        // Record which tier the access came from so entitlements can key off it.
        $user->forceFill(['subscription_plan' => $payment->plan])->save();

        $payment->update([
            'status' => SubscriptionPayment::STATUS_PAID,
            'paid_at' => Carbon::now(),
            'period_start' => Carbon::today(),
            'period_end' => $newUntil,
            'payload' => $event,
        ]);

        $this->audit->log('subscription_payment_paid', $payment,
            sprintf('Payment confirmed: %s × %d month(s), ₱%s — access until %s',
                $payment->plan, $months, number_format($payment->amount / 100, 2), $newUntil->toDateString()),
            null,
            ['plan' => $payment->plan, 'months' => $months, 'amount' => $payment->amount, 'until' => $newUntil->toDateString()],
            $payment->user_id,
        );

        Log::info('Subscription extended via PayMongo', [
            'user' => $user->id,
            'plan' => $payment->plan,
            'months' => $months,
            'until' => $newUntil->toDateString(),
        ]);

        return response()->json(['ok' => true]);
    }
}
