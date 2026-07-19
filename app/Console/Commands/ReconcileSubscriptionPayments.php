<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPayment;
use App\Services\AuditLogger;
use App\Services\PayMongoService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Safety net for payments that were never confirmed in-app.
 *
 * The webhook can be lost and the teacher may simply close the tab instead of
 * returning to the site, and either way they have paid and are owed access.
 * This sweeps every still-open checkout, asks PayMongo what really happened,
 * and settles the ones that were paid. Run it on a schedule: it is the only
 * path that does not depend on the customer coming back.
 */
class ReconcileSubscriptionPayments extends Command
{
    protected $signature = 'subscriptions:reconcile
                            {--hours=72 : Only inspect payments opened within this many hours}
                            {--dry-run : Report what would change without touching anything}';

    protected $description = 'Settle paid-but-unconfirmed PayMongo checkouts and close stale ones';

    public function handle(PayMongoService $paymongo, AuditLogger $audit): int
    {
        if (! $paymongo->isConfigured()) {
            $this->error('PayMongo is not configured — nothing to reconcile.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $open = SubscriptionPayment::where('status', SubscriptionPayment::STATUS_PENDING)
            ->whereNotNull('provider_reference')
            ->where('created_at', '>=', Carbon::now()->subHours((int) $this->option('hours')))
            ->get();

        if ($open->isEmpty()) {
            $this->info('No open checkouts to reconcile.');

            return self::SUCCESS;
        }

        $this->info("Checking {$open->count()} open checkout(s)...");
        $settled = $unpaid = $unreachable = 0;

        foreach ($open as $payment) {
            $session = $paymongo->retrieveCheckoutSession((string) $payment->provider_reference);

            // Could not ask: leave it pending and try again next run. Writing it
            // off here is what would lose a real payment.
            if ($session === null) {
                $this->warn("  #{$payment->id} — lookup failed, left pending");
                $unreachable++;

                continue;
            }

            if (! $paymongo->checkoutSessionIsPaid($session)) {
                $this->line("  #{$payment->id} — not paid");
                $unpaid++;

                continue;
            }

            $amountPaid = $paymongo->paidAmount($session);

            if ($amountPaid !== null && $amountPaid < (int) $payment->amount) {
                $this->warn(sprintf('  #%d — UNDERPAID ₱%s of ₱%s, needs review',
                    $payment->id, number_format($amountPaid / 100, 2), number_format($payment->amount / 100, 2)));

                continue;
            }

            if ($dryRun) {
                $this->line("  #{$payment->id} — would settle (paid, unconfirmed)");
                $settled++;

                continue;
            }

            $this->grant($payment, $session, $audit);
            $this->info("  #{$payment->id} — settled");
            $settled++;
        }

        $this->newLine();
        $this->info(sprintf('%s%d settled, %d still unpaid, %d unreachable.',
            $dryRun ? '[dry run] ' : '', $settled, $unpaid, $unreachable));

        return self::SUCCESS;
    }

    /**
     * Mirrors the controller's settle(): locked, idempotent, and an upgrade
     * moves the tier without extending the paid-for period.
     *
     * @param  array<string, mixed>  $session
     */
    private function grant(SubscriptionPayment $payment, array $session, AuditLogger $audit): void
    {
        $result = DB::transaction(function () use ($payment, $session) {
            $fresh = SubscriptionPayment::whereKey($payment->getKey())->lockForUpdate()->first();

            if (! $fresh || $fresh->status === SubscriptionPayment::STATUS_PAID) {
                return null;
            }

            $user = $fresh->user;
            $months = max(1, (int) $fresh->months);

            $newUntil = $fresh->isUpgrade()
                ? $user->subscribed_until
                : $user->extendSubscription($months);

            $user->forceFill(['subscription_plan' => $fresh->plan])->save();

            $fresh->update([
                'status' => SubscriptionPayment::STATUS_PAID,
                'paid_at' => Carbon::now(),
                'period_start' => Carbon::today(),
                'period_end' => $newUntil,
                'payload' => ['source' => 'scheduled_reconcile', 'session' => $session],
            ]);

            return [$fresh, $months, $newUntil];
        });

        if (! $result) {
            return;
        }

        [$fresh, $months, $newUntil] = $result;

        $audit->log('subscription_payment_paid', $fresh,
            sprintf('Payment recovered by reconciliation: %s x %d month(s), P%s - access until %s',
                $fresh->plan, $months, number_format($fresh->amount / 100, 2), $newUntil?->toDateString()),
            null,
            [
                'kind' => $fresh->kind,
                'plan' => $fresh->plan,
                'months' => $months,
                'amount' => $fresh->amount,
                'until' => $newUntil?->toDateString(),
                'confirmed_via' => 'scheduled_reconcile',
            ],
            $fresh->user_id,
        );

        Log::info('Subscription recovered by reconciliation', [
            'user' => $fresh->user_id,
            'payment' => $fresh->id,
            'until' => $newUntil?->toDateString(),
        ]);
    }
}
