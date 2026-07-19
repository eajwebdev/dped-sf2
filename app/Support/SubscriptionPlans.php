<?php

namespace App\Support;

use App\Models\Setting;

/**
 * The three subscription tiers and the multi-month billing rule.
 *
 * Prices are admin-editable (Admin → Settings → Payments) and fall back to the
 * defaults below. Paying several months up front earns DISCOUNT_PER_EXTRA_MONTH
 * for each month beyond the first, capped at MAX_DISCOUNT_PERCENT.
 */
class SubscriptionPlans
{
    public const STARTER = 'starter';

    public const PROFESSIONAL = 'professional';

    public const ENTERPRISE = 'enterprise';

    /** Percent knocked off for every month bought beyond the first. */
    public const DISCOUNT_PER_EXTRA_MONTH = 3;

    /** Ceiling on the advance-payment discount, however many months are bought. */
    public const MAX_DISCOUNT_PERCENT = 30;

    /** Longest advance purchase allowed in one checkout. */
    public const MAX_MONTHS = 12;

    /**
     * Each perk carries a `live` flag: a live perk is usable the day you pay,
     * an upcoming one is shown with an explicit "On release" badge so a tier
     * never sells something that does not exist yet.
     *
     * @return array<string, array{key:string, name:string, default:int, tagline:string, perks:array<int,array{label:string,live:bool}>}>
     */
    public static function all(): array
    {
        return [
            self::STARTER => [
                'key' => self::STARTER,
                'name' => 'Starter',
                'default' => 19900,
                'tagline' => 'Everything a class adviser needs today.',
                'perks' => [
                    ['label' => 'SF1 — School Register', 'live' => true],
                    ['label' => 'SF2 — Daily Attendance', 'live' => true],
                    ['label' => 'QR check-in + printable QR ID cards', 'live' => true],
                    ['label' => 'Unlimited classes & learners', 'live' => true],
                    ['label' => 'Weekly schedule & scan portal', 'live' => true],
                ],
            ],
            self::PROFESSIONAL => [
                'key' => self::PROFESSIONAL,
                'name' => 'Professional',
                'default' => 26900,
                'tagline' => 'For advisers who want their whole form load automated.',
                'perks' => [
                    ['label' => 'Everything in Starter', 'live' => true],
                    ['label' => 'SF3 — Books Issued & Returned', 'live' => true],
                    ['label' => 'SF5 — Promotion & Level of Proficiency', 'live' => true],
                    ['label' => 'Advanced reports — class insights dashboard', 'live' => true],
                ],
            ],
            self::ENTERPRISE => [
                'key' => self::ENTERPRISE,
                'name' => 'Enterprise',
                'default' => 44900,
                'tagline' => 'For schools standardising every form across departments.',
                'perks' => [
                    ['label' => 'Everything in Professional', 'live' => true],
                    ['label' => 'All School Form modules on release', 'live' => false],
                    ['label' => 'School-wide analytics', 'live' => false],
                    ['label' => 'Priority support', 'live' => false],
                ],
            ],
        ];
    }

    /**
     * The minimum plan each gated School Form module belongs to. SF1 and SF2
     * are part of every plan; SF3 and SF5 ship with Professional and up.
     */
    public const MODULE_MIN_PLAN = [
        'sf3' => self::PROFESSIONAL,
        'sf5' => self::PROFESSIONAL,
        'insights' => self::PROFESSIONAL,
    ];

    /** Whether a subscriber on $plan may use $module (ungated modules: yes). */
    public static function planCovers(string $plan, string $module): bool
    {
        $required = self::MODULE_MIN_PLAN[strtolower($module)] ?? null;

        return $required === null || self::rank($plan) >= self::rank($required);
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Tier order, cheapest first. Upgrades move up this ladder; nothing may
     * move down mid-term, because the time has already been paid for.
     */
    public static function rank(string $plan): int
    {
        return array_search(self::find($plan)['key'], self::keys(), true) ?: 0;
    }

    public static function isUpgradeFrom(string $current, string $target): bool
    {
        return self::rank($target) > self::rank($current);
    }

    /**
     * Cost of moving an active subscription up a tier for the time it has left.
     *
     * The subscriber has already paid for their remaining months at the old
     * tier, so they top up only the difference: (new monthly − current monthly)
     * × months remaining. Upgrading a ₱199 plan with 3 months left to a ₱449
     * plan costs (449 − 199) × 3 = ₱750, and the end date does not move.
     *
     * No advance-payment discount applies — that was already granted on the
     * original purchase and the months here are the same ones.
     *
     * @return array{months:int, from:string, to:string, monthly_difference:int, subtotal:int, promo:int, total:int}
     */
    public static function upgradeQuote(string $current, string $target, int $remainingMonths): array
    {
        $months = max(1, $remainingMonths);
        $difference = max(0, self::monthlyPrice($target) - self::monthlyPrice($current));
        $subtotal = $difference * $months;

        // The promo of the tier being moved to — that is what is being bought.
        $promo = self::promoPercent($target);
        $total = (int) round($subtotal * (100 - $promo) / 100);

        return [
            'months' => $months,
            'from' => $current,
            'to' => $target,
            'monthly_difference' => $difference,
            'subtotal' => $subtotal,
            'promo' => $promo,
            'total' => $total,
        ];
    }

    public static function exists(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::all());
    }

    public static function find(string $key): array
    {
        return self::all()[$key] ?? self::all()[self::STARTER];
    }

    /** Settings key holding a plan's admin-set monthly price. */
    public static function settingKey(string $plan): string
    {
        return "subscription_price_{$plan}_centavos";
    }

    /** Settings key holding a plan's admin-set promo discount. */
    public static function discountSettingKey(string $plan): string
    {
        return "subscription_discount_{$plan}_percent";
    }

    /** A plan's monthly list price in centavos. */
    public static function monthlyPrice(string $plan): int
    {
        $meta = self::find($plan);

        return (int) Setting::get(self::settingKey($meta['key']), $meta['default']);
    }

    /**
     * The promo discount on a plan, as a whole percent.
     *
     * Falls back to the site-wide discount when a plan has none of its own, so
     * a previously configured global promo keeps working until it is replaced
     * by per-plan values.
     */
    public static function promoPercent(string $plan): int
    {
        $meta = self::find($plan);
        $value = Setting::get(self::discountSettingKey($meta['key']), Setting::discountPercent());

        return max(0, min(100, (int) $value));
    }

    /** A plan's actual monthly price after its promo discount, in centavos. */
    public static function effectiveMonthlyPrice(string $plan): int
    {
        return (int) round(self::monthlyPrice($plan) * (100 - self::promoPercent($plan)) / 100);
    }

    /**
     * Advance-payment discount for a month count: 3% per month beyond the
     * first, never more than MAX_DISCOUNT_PERCENT.
     */
    public static function discountFor(int $months): int
    {
        $months = self::clampMonths($months);

        return min(self::MAX_DISCOUNT_PERCENT, ($months - 1) * self::DISCOUNT_PER_EXTRA_MONTH);
    }

    public static function clampMonths(int $months): int
    {
        return max(1, min(self::MAX_MONTHS, $months));
    }

    /**
     * What the subscriber actually pays, in centavos, for a plan × months
     * after the advance-payment discount. Any admin-wide promo discount is
     * applied on top of it.
     *
     * @return array{months:int, monthly:int, subtotal:int, discount:int, promo:int, total:int, saved:int}
     */
    public static function quote(string $plan, int $months): array
    {
        $months = self::clampMonths($months);
        $monthly = self::monthlyPrice($plan);
        $subtotal = $monthly * $months;

        $discount = self::discountFor($months);
        $promo = self::promoPercent($plan);

        // Both reductions come off the subtotal; they stack multiplicatively so
        // a 30% advance discount plus a 10% promo never exceeds the subtotal.
        $total = (int) round($subtotal * (100 - $discount) / 100 * (100 - $promo) / 100);

        return [
            'months' => $months,
            'monthly' => $monthly,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'promo' => $promo,
            'total' => $total,
            'saved' => $subtotal - $total,
        ];
    }
}
