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
     * Cost of moving an active subscription up a tier, prorated by day.
     *
     * Follows the standard SaaS proration rule: the subscriber is credited for
     * the unused part of what they already bought and charged for the same
     * period on the new tier, so the top-up is the gap between the two full
     * terms, scaled by how much of the term is left. The end date never moves.
     *
     * Both sides are priced with the same term length, so the advance-payment
     * discount and each tier's promo apply to both. That guarantees the
     * property customers actually check: upgrading mid-term costs exactly what
     * choosing the higher tier from the start would have, never more. Pricing
     * only the raw list difference would quietly make upgrading the more
     * expensive route and punish the customer for starting small.
     *
     * @param  int    $termMonths     Length of the term already bought.
     * @param  float  $remainingRatio Share of that term still unused, 0..1.
     * @return array{months:int, from:string, to:string, monthly_difference:int, subtotal:int, promo:int, total:int, term_months:int, remaining_ratio:float, current_term_total:int, target_term_total:int}
     */
    public static function upgradeQuote(string $current, string $target, int $termMonths, float $remainingRatio = 1.0): array
    {
        $termMonths = self::clampMonths(max(1, $termMonths));
        $ratio = max(0.0, min(1.0, $remainingRatio));

        // What the whole term costs on each tier, promos and advance discount
        // included — the two numbers a customer would compare when choosing.
        $currentTotal = self::quote($current, $termMonths)['total'];
        $targetTotal = self::quote($target, $termMonths)['total'];

        $gap = max(0, $targetTotal - $currentTotal);
        $total = (int) round($gap * $ratio);

        // Per-month figure for display only; the charge above is the authority.
        $monthlyDifference = (int) round($gap / $termMonths);

        return [
            'months' => max(1, (int) ceil($termMonths * $ratio)),
            'from' => $current,
            'to' => $target,
            'monthly_difference' => $monthlyDifference,
            'subtotal' => $gap,
            'promo' => self::promoPercent($target),
            'total' => $total,

            'term_months' => $termMonths,
            'remaining_ratio' => $ratio,
            'current_term_total' => $currentTotal,
            'target_term_total' => $targetTotal,
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
