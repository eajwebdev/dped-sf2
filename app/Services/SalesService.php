<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Subscription/sales figures for the admin dashboard.
 *
 * States mirror User::subscriptionState(): a teacher is billing-enrolled once
 * they have a trial_ends_at or subscribed_until. "Late due" is an enrolled
 * teacher whose trial or paid period has lapsed — the ones worth chasing.
 */
class SalesService
{
    public function overview(): array
    {
        $today = Carbon::today();

        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('status', User::STATUS_APPROVED)
            ->get();

        // Comped accounts are outside the funnel: never "trial", never "late due".
        $free = $teachers->filter(fn (User $u) => $u->free_access);
        $billable = $teachers->reject(fn (User $u) => $u->free_access);

        $active = $billable->filter(fn (User $u) => $u->isSubscribed());
        $trial = $billable->filter(fn (User $u) => ! $u->isSubscribed() && $u->onTrial());
        $lateDue = $billable->filter(fn (User $u) => $u->isBillingEnrolled() && ! $u->isSubscribed() && ! $u->onTrial());

        $paid = SubscriptionPayment::where('status', SubscriptionPayment::STATUS_PAID);

        return [
            'activeCount' => $active->count(),
            'trialCount' => $trial->count(),
            'lateDueCount' => $lateDue->count(),
            'freeCount' => $free->count(),
            'pendingCount' => User::where('role', User::ROLE_TEACHER)->where('status', User::STATUS_PENDING)->count(),

            // Recognised revenue, not invoiced totals.
            'revenueAllTime' => (clone $paid)->sum('amount'),
            'revenueThisMonth' => (clone $paid)->whereBetween('paid_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])->sum('amount'),

            'mrr' => $active->count() * Setting::effectivePriceCentavos(),
            'priceCentavos' => Setting::priceCentavos(),
            'discountPercent' => Setting::discountPercent(),
            'effectivePriceCentavos' => Setting::effectivePriceCentavos(),

            'expiringSoon' => $this->expiringSoon($billable, $today),
            'lateDueList' => $lateDue->sortBy(fn (User $u) => $u->subscribed_until ?? $u->trial_ends_at)->take(8)->values(),
            'recentPayments' => SubscriptionPayment::with('user')->latest('paid_at')->take(5)->get(),
        ];
    }

    /** Subscribed teachers whose paid period lapses within a week — renewal targets. */
    private function expiringSoon(Collection $teachers, Carbon $today): Collection
    {
        return $teachers
            ->filter(fn (User $u) => $u->isSubscribed()
                && $u->subscribed_until->lte($today->copy()->addDays(7)))
            ->sortBy('subscribed_until')
            ->take(8)
            ->values();
    }
}
