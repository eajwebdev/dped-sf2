<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PayMongoService;
use App\Support\SubscriptionPlans;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        /*
         * While a subscription is running the page becomes an upgrade screen:
         * the current tier is marked as owned, lower tiers are unavailable, and
         * higher tiers are priced as the top-up for the months left.
         */
        $subscribed = $user->isSubscribed();
        $remainingMonths = $user->remainingSubscriptionMonths();
        $upgradeQuotes = [];

        if ($subscribed) {
            foreach (SubscriptionPlans::keys() as $plan) {
                if (SubscriptionPlans::isUpgradeFrom($user->currentPlan(), $plan)) {
                    $upgradeQuotes[$plan] = SubscriptionPlans::upgradeQuote(
                        $user->currentPlan(), $plan, $remainingMonths
                    );
                }
            }
        }

        return view('subscribe.show', [
            'plans' => SubscriptionPlans::all(),
            'quotes' => $quotes,
            'maxMonths' => SubscriptionPlans::MAX_MONTHS,
            'perMonthDiscount' => SubscriptionPlans::DISCOUNT_PER_EXTRA_MONTH,
            'maxDiscount' => SubscriptionPlans::MAX_DISCOUNT_PERCENT,
            'currentPlan' => $user->currentPlan(),
            'configured' => $this->paymongo->isConfigured(),

            'subscribed' => $subscribed,
            'subscribedUntil' => $user->subscribed_until,
            'remainingMonths' => $remainingMonths,
            'upgradeQuotes' => $upgradeQuotes,
            'canRenew' => $user->canRenew(),
            'renewalWindowDays' => User::RENEWAL_WINDOW_DAYS,
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

        /*
         * Close out anything left open from a previous attempt first. If one of
         * those was actually paid, charging again would take a teacher's money
         * twice for the same period — so that case settles the old payment and
         * stops here instead of opening a new checkout.
         */
        if ($settled = $this->reconcileOpenPayments($user)) {
            return redirect()->route('dashboard')->with('success', sprintf(
                'You had already paid — your %s plan is active until %s. You have not been charged again.',
                SubscriptionPlans::find($settled->plan)['name'],
                $user->fresh()->subscribed_until->format('F j, Y'),
            ));
        }

        $validated = $request->validate([
            'plan' => ['required', Rule::in(SubscriptionPlans::keys())],
            'months' => ['required', 'integer', 'min:1', 'max:'.SubscriptionPlans::MAX_MONTHS],
        ]);

        $plan = $validated['plan'];

        /*
         * An active subscriber has already paid for this period, so buying it
         * again would stack a second term on top. Their only move is up a tier,
         * priced as the difference for the months they have left — and even the
         * decision of which case applies is made here, never from the form.
         */
        $upgrading = $user->isSubscribed() && $user->currentPlan() !== $plan;

        if ($user->isSubscribed() && ! $upgrading && ! $user->canRenew()) {
            return back()->with('error', sprintf(
                'You are already on the %s plan until %s. You can renew from %s, or upgrade to a higher plan any time.',
                SubscriptionPlans::find($plan)['name'],
                $user->subscribed_until->format('M j, Y'),
                $user->subscribed_until->copy()->subDays(User::RENEWAL_WINDOW_DAYS)->format('M j, Y'),
            ));
        }

        if ($upgrading && ! $user->canUpgradeTo($plan)) {
            return back()->with('error', sprintf(
                'You cannot move from %s to %s mid-term — that time is already paid for. You can switch when your subscription renews on %s.',
                SubscriptionPlans::find($user->currentPlan())['name'],
                SubscriptionPlans::find($plan)['name'],
                $user->subscribed_until->format('M j, Y'),
            ));
        }

        // The quote is recomputed here so a tampered form cannot set the price.
        if ($upgrading) {
            $upgrade = SubscriptionPlans::upgradeQuote(
                $user->currentPlan(), $plan, $user->remainingSubscriptionMonths()
            );
            $quote = ['months' => $upgrade['months'], 'total' => $upgrade['total'], 'discount' => $upgrade['promo']];
        } else {
            $quote = SubscriptionPlans::quote($plan, (int) $validated['months']);
        }

        $payment = SubscriptionPayment::create([
            'user_id' => $user->id,
            'provider' => 'paymongo',
            'plan' => $plan,
            'kind' => $upgrading ? SubscriptionPayment::KIND_UPGRADE : SubscriptionPayment::KIND_PURCHASE,
            'previous_plan' => $upgrading ? $user->currentPlan() : null,
            'months' => $quote['months'],
            'amount' => $quote['total'],
            'discount_percent' => $quote['discount'],
            'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $this->audit->log('subscription_checkout_started', $payment,
            $upgrading
                ? sprintf('Upgrade started: %s → %s for %d month(s) remaining, ₱%s',
                    $user->currentPlan(), $plan, $quote['months'], number_format($quote['total'] / 100, 2))
                : sprintf('Checkout started: %s × %d month(s), ₱%s',
                    $plan, $quote['months'], number_format($quote['total'] / 100, 2)),
            null,
            [
                'kind' => $payment->kind,
                'previous_plan' => $payment->previous_plan,
                'plan' => $plan,
                'months' => $quote['months'],
                'amount' => $quote['total'],
                'discount_percent' => $quote['discount'],
            ],
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

    /**
     * Return page from the gateway.
     *
     * This does NOT trust the redirect itself — anyone can visit this URL. It
     * asks PayMongo whether the session was really paid, and only then grants
     * access. Relying on the webhook alone left a paying teacher locked out
     * whenever it did not arrive: it never can locally (PayMongo cannot reach
     * a localhost URL) and in production it can be delayed or dropped.
     */
    public function success(Request $request): RedirectResponse
    {
        $payment = SubscriptionPayment::where('user_id', $request->user()->id)
            ->where('status', SubscriptionPayment::STATUS_PENDING)
            ->whereNotNull('provider_reference')
            ->latest('id')
            ->first();

        if (! $payment) {
            // Already settled by the webhook that beat us here — nothing to do.
            return redirect()->route('dashboard')
                ->with('success', 'Thank you! Your subscription is active.');
        }

        $session = $this->paymongo->retrieveCheckoutSession((string) $payment->provider_reference);

        if ($this->paymongo->checkoutSessionIsPaid($session)) {
            if (! $this->settle($payment, ['source' => 'return_url', 'session' => $session], $this->paymongo->paidAmount($session))) {
                return redirect()->route('subscribe.show')->with('error',
                    'We could not confirm the full amount for this payment. Nothing further has been charged — '
                    .'please contact support and we will sort it out.');
            }

            return redirect()->route('dashboard')->with('success', sprintf(
                'Payment confirmed — your %s plan is active until %s.',
                SubscriptionPlans::find($payment->plan)['name'],
                $payment->user->fresh()->subscribed_until->format('F j, Y'),
            ));
        }

        // Genuinely still processing (e-wallets can lag). The webhook remains
        // the backstop, so say so honestly rather than claiming success.
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

        /*
         * The event carries the amount either directly (payment.paid) or inside
         * the checkout session's payments (checkout_session.payment.paid). Null
         * when neither is present, which skips the amount check rather than
         * failing a legitimate payment on a payload shape we did not expect.
         */
        $resource = data_get($event, 'data.attributes.data.attributes');
        $amountPaid = data_get($resource, 'amount');

        if ($amountPaid === null && is_array(data_get($resource, 'payments'))) {
            $paid = collect(data_get($resource, 'payments'))
                ->filter(fn ($p) => data_get($p, 'attributes.status') === 'paid');

            $amountPaid = $paid->isNotEmpty() ? (int) $paid->sum(fn ($p) => (int) data_get($p, 'attributes.amount', 0)) : null;
        }

        return response()->json([
            'ok' => true,
            'settled' => $this->settle($payment, $event, $amountPaid === null ? null : (int) $amountPaid),
        ]);
    }

    /**
     * Settle or close every checkout this teacher left open.
     *
     * A pending row means a checkout was opened but never confirmed here. It
     * may still have been paid — the confirmation can be lost, and PayMongo's
     * single-use sources mean the old link now errors with "Source has consumed
     * status" rather than completing. Each one is therefore checked against
     * PayMongo: paid ones are settled, the rest are marked cancelled so they
     * stop shadowing future attempts.
     *
     * @return SubscriptionPayment|null  A payment that turned out to be paid.
     */
    private function reconcileOpenPayments(User $user): ?SubscriptionPayment
    {
        $open = SubscriptionPayment::where('user_id', $user->id)
            ->where('status', SubscriptionPayment::STATUS_PENDING)
            ->get();

        foreach ($open as $payment) {
            if (blank($payment->provider_reference)) {
                // Never reached the gateway — nothing to check, nothing charged.
                $payment->update(['status' => SubscriptionPayment::STATUS_CANCELLED]);

                continue;
            }

            $session = $this->paymongo->retrieveCheckoutSession((string) $payment->provider_reference);

            // A lookup that fails (network, API hiccup) must leave the row alone:
            // guessing "unpaid" here is what would cause a double charge.
            if ($session === null) {
                continue;
            }

            if ($this->paymongo->checkoutSessionIsPaid($session)) {
                if ($this->settle($payment, ['source' => 'checkout_reconcile', 'session' => $session], $this->paymongo->paidAmount($session))) {
                    return $payment->fresh();
                }

                // Underpaid and flagged: do not open a fresh charge on top of it.
                continue;
            }

            $payment->update(['status' => SubscriptionPayment::STATUS_CANCELLED]);
            $this->audit->log('subscription_checkout_cancelled', $payment,
                'Abandoned checkout closed when a new one was started');
        }

        return null;
    }

    /**
     * Grant the access a confirmed payment bought, exactly once.
     *
     * Both the webhook and the return page can reach a paid session, and they
     * can arrive at the same moment. The row is therefore locked and its
     * status re-read inside the transaction: whichever path gets there first
     * does the work, the other sees STATUS_PAID and stops. Without the lock a
     * race would extend the subscription twice for a single payment.
     *
     * @param  array<string, mixed>  $payload
     * @return bool  True when this call is the one that granted access.
     */
    private function settle(SubscriptionPayment $payment, array $payload, ?int $amountPaid = null): bool
    {
        /*
         * Grant on the strength of what was actually settled, never on what the
         * app hoped would be charged. A session completed for less than the
         * quote must not buy a subscription; it is flagged for an admin instead
         * of being silently honoured or silently dropped.
         */
        if ($amountPaid !== null && $amountPaid < (int) $payment->amount) {
            Log::warning('PayMongo underpayment ignored', [
                'payment' => $payment->id,
                'expected' => $payment->amount,
                'paid' => $amountPaid,
            ]);

            $this->audit->log('subscription_payment_underpaid', $payment,
                sprintf('Paid ₱%s against a quote of ₱%s — access not granted, needs review',
                    number_format($amountPaid / 100, 2), number_format($payment->amount / 100, 2)),
                null,
                ['expected' => $payment->amount, 'paid' => $amountPaid],
                $payment->user_id,
            );

            return false;
        }

        $granted = DB::transaction(function () use ($payment, $payload) {
            $fresh = SubscriptionPayment::whereKey($payment->getKey())->lockForUpdate()->first();

            if (! $fresh || $fresh->status === SubscriptionPayment::STATUS_PAID) {
                return null;
            }

            $user = $fresh->user;
            $months = max(1, (int) $fresh->months);

            /*
             * An upgrade buys a better tier for time already paid for, so the
             * end date stays put and only the plan moves. Extending it here
             * would hand out free months every time someone upgraded.
             */
            $newUntil = $fresh->isUpgrade()
                ? $user->subscribed_until
                : $user->extendSubscription($months);

            // Record which tier the access came from so entitlements key off it.
            $user->forceFill(['subscription_plan' => $fresh->plan])->save();

            $fresh->update([
                'status' => SubscriptionPayment::STATUS_PAID,
                'paid_at' => Carbon::now(),
                'period_start' => Carbon::today(),
                'period_end' => $newUntil,
                'payload' => $payload,
            ]);

            return [$fresh, $months, $newUntil];
        });

        if (! $granted) {
            return false;
        }

        [$fresh, $months, $newUntil] = $granted;

        $this->audit->log('subscription_payment_paid', $fresh,
            $fresh->isUpgrade()
                ? sprintf('Upgrade confirmed: %s → %s for %d month(s), ₱%s — access still until %s',
                    $fresh->previous_plan, $fresh->plan, $months,
                    number_format($fresh->amount / 100, 2), $newUntil?->toDateString())
                : sprintf('Payment confirmed: %s × %d month(s), ₱%s — access until %s',
                    $fresh->plan, $months, number_format($fresh->amount / 100, 2), $newUntil->toDateString()),
            null,
            [
                'kind' => $fresh->kind,
                'previous_plan' => $fresh->previous_plan,
                'plan' => $fresh->plan,
                'months' => $months,
                'amount' => $fresh->amount,
                'until' => $newUntil?->toDateString(),
                'confirmed_via' => data_get($payload, 'source', 'webhook'),
            ],
            $fresh->user_id,
        );

        Log::info('Subscription extended via PayMongo', [
            'user' => $fresh->user_id,
            'plan' => $fresh->plan,
            'months' => $months,
            'until' => $newUntil?->toDateString(),
            'via' => data_get($payload, 'source', 'webhook'),
        ]);

        return true;
    }
}
