<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Builds the notification list behind the header bell.
 *
 * These are derived on the fly from account state rather than stored rows: a
 * teacher's subscription window is a fact about the account, so there is
 * nothing to keep in sync and nothing to go stale.
 */
class NotificationService
{
    /** Start nudging this many days before access lapses. */
    public const EXPIRY_WARNING_DAYS = 14;

    /** Below this, the reminder escalates to a warning tone. */
    public const EXPIRY_URGENT_DAYS = 3;

    /**
     * @return array<int, array{id:string,level:string,title:string,body:string,url:?string,cta:?string}>
     */
    public function for(User $user): array
    {
        // Admins do not carry a subscription, so the bell stays quiet for them.
        if (! $user->isTeacher()) {
            return [];
        }

        return array_values(array_filter([
            $this->subscriptionNotice($user),
            $this->pendingApprovalNotice($user),
        ]));
    }

    public function unreadCount(User $user): int
    {
        return count($this->for($user));
    }

    /** The most severe level present, used to colour the bell's dot. */
    public function highestLevel(User $user): ?string
    {
        $levels = array_column($this->for($user), 'level');

        foreach (['danger', 'warning', 'info'] as $level) {
            if (in_array($level, $levels, true)) {
                return $level;
            }
        }

        return null;
    }

    /**
     * Trial and subscription both resolve to "access ends on <date>", which is
     * the thing a teacher actually needs to know.
     */
    private function subscriptionNotice(User $user): ?array
    {
        $state = $user->subscriptionState();

        if ($state === 'expired') {
            return [
                'id' => 'subscription-expired',
                'level' => 'danger',
                'title' => 'Your access has ended',
                'body' => 'Subscribe to get back into your classes, attendance and SF2 reports.',
                'url' => route('subscribe.show'),
                'cta' => 'Subscribe now',
            ];
        }

        // Comped and admin-managed accounts have no expiry to warn about.
        if (! in_array($state, ['trial', 'active'], true)) {
            return null;
        }

        $endsAt = $state === 'trial' ? $user->trial_ends_at : $user->subscribed_until;
        if (! $endsAt) {
            return null;
        }

        $daysLeft = (int) Carbon::today()->diffInDays($endsAt->copy()->startOfDay(), false);

        if ($daysLeft > self::EXPIRY_WARNING_DAYS) {
            return null;
        }

        $label = $state === 'trial' ? 'free trial' : 'subscription';

        return [
            'id' => "subscription-expiring-{$endsAt->toDateString()}",
            'level' => $daysLeft <= self::EXPIRY_URGENT_DAYS ? 'danger' : 'warning',
            'title' => match (true) {
                $daysLeft <= 0 => 'Your '.$label.' ends today',
                $daysLeft === 1 => 'Your '.$label.' ends tomorrow',
                default => "Your {$label} ends in {$daysLeft} days",
            },
            'body' => 'Access runs until '.$endsAt->format('M j, Y').'. Renew before then to keep your classes and reports.',
            'url' => route('subscribe.show'),
            'cta' => $state === 'trial' ? 'Subscribe' : 'Renew',
        ];
    }

    private function pendingApprovalNotice(User $user): ?array
    {
        if ($user->subscriptionState() !== User::STATUS_PENDING) {
            return null;
        }

        return [
            'id' => 'awaiting-approval',
            'level' => 'info',
            'title' => 'Waiting for approval',
            'body' => 'Your administrator is reviewing the school ID you uploaded. You will get access as soon as it is approved.',
            'url' => null,
            'cta' => null,
        ];
    }
}
