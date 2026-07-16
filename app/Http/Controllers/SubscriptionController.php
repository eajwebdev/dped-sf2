<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubscriptionController extends Controller
{
    public function __construct(private readonly PayMongoService $paymongo) {}

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

        return view('subscribe.show', [
            'price' => $this->paymongo->price() / 100,
            'configured' => $this->paymongo->isConfigured(),
        ]);
    }

    /** Start a PayMongo checkout for one month. */
    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $this->paymongo->isConfigured()) {
            return back()->with('error', 'Online payment is not available yet. Please contact your administrator.');
        }

        $payment = SubscriptionPayment::create([
            'user_id' => $user->id,
            'provider' => 'paymongo',
            'amount' => $this->paymongo->price(),
            'currency' => 'PHP',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        try {
            $session = $this->paymongo->createCheckoutSession($user, (string) $payment->id);
        } catch (Throwable $e) {
            Log::error('PayMongo checkout failed', ['user' => $user->id, 'error' => $e->getMessage()]);
            $payment->update(['status' => SubscriptionPayment::STATUS_FAILED]);

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

    public function cancel(): RedirectResponse
    {
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

        if (! in_array($type, ['checkout_session.payment.paid', 'payment.paid'], true)) {
            return response()->json(['ok' => true, 'ignored' => $type]);
        }

        $reference = data_get($event, 'data.attributes.data.attributes.reference_number');
        $payment = $reference
            ? SubscriptionPayment::find($reference)
            : SubscriptionPayment::where('provider_reference', data_get($event, 'data.attributes.data.id'))->first();

        if (! $payment) {
            return response()->json(['ok' => true, 'message' => 'No matching payment']);
        }

        // Idempotent: ignore replays of an already-processed payment.
        if ($payment->status === SubscriptionPayment::STATUS_PAID) {
            return response()->json(['ok' => true, 'message' => 'Already processed']);
        }

        $user = $payment->user;
        $newUntil = $user->extendSubscription(1);

        $payment->update([
            'status' => SubscriptionPayment::STATUS_PAID,
            'paid_at' => Carbon::now(),
            'period_start' => Carbon::today(),
            'period_end' => $newUntil,
            'payload' => $event,
        ]);

        Log::info('Subscription extended via PayMongo', [
            'user' => $user->id,
            'until' => $newUntil->toDateString(),
        ]);

        return response()->json(['ok' => true]);
    }
}
