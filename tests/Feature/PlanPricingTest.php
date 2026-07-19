<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The three tiers are admin-managed: each has its own price and its own promo
 * discount, and both must reach the public landing page and the checkout quote
 * without a deploy.
 */
class PlanPricingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'module' => 'pricing',
            'prices' => ['starter' => 149, 'professional' => 299, 'enterprise' => 599],
            'discounts' => ['starter' => 0, 'professional' => 25, 'enterprise' => 10],
        ], $overrides);
    }

    public function test_an_admin_sets_each_tier_price_and_discount(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->payload())
            ->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('success');

        $this->assertSame(14900, SubscriptionPlans::monthlyPrice('starter'));
        $this->assertSame(29900, SubscriptionPlans::monthlyPrice('professional'));
        $this->assertSame(59900, SubscriptionPlans::monthlyPrice('enterprise'));

        $this->assertSame(0, SubscriptionPlans::promoPercent('starter'));
        $this->assertSame(25, SubscriptionPlans::promoPercent('professional'));
        $this->assertSame(10, SubscriptionPlans::promoPercent('enterprise'));

        // 299 less 25% = 224.25
        $this->assertSame(22425, SubscriptionPlans::effectiveMonthlyPrice('professional'));
    }

    public function test_new_prices_and_discounts_show_on_the_public_landing_page(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.update'), $this->payload());

        // The landing page sends signed-in users to their dashboard, so the
        // pricing that matters is what a prospective customer sees as a guest.
        auth()->logout();

        $this->get(route('landing'))
            ->assertOk()
            // Discounted Professional price, its struck-through list price and the badge.
            ->assertSee('₱224.25', false)
            ->assertSee('₱299', false)
            ->assertSee('25% off')
            // Starter has no promo, so it shows its plain price and no badge.
            ->assertSee('₱149', false);
    }

    public function test_centavos_are_shown_when_a_discount_creates_them(): void
    {
        // ₱1.00 less 10% = ₱0.90. Rounded to whole pesos both render "₱1" and
        // the discount vanishes from the page.
        $this->actingAs($this->admin())->put(route('admin.settings.update'), $this->payload([
            'prices' => ['starter' => 1],
            'discounts' => ['starter' => 10],
        ]));

        auth()->logout();

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('₱0.90', false)
            ->assertSee('10% off');
    }

    public function test_the_discount_is_charged_at_checkout_not_just_advertised(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.update'), $this->payload());

        // One month of Professional: 299 less the 25% promo.
        $quote = SubscriptionPlans::quote('professional', 1);
        $this->assertSame(25, $quote['promo']);
        $this->assertSame(22425, $quote['total']);

        // Starter carries no promo, so its total is the plain price.
        $this->assertSame(0, SubscriptionPlans::quote('starter', 1)['promo']);
        $this->assertSame(14900, SubscriptionPlans::quote('starter', 1)['total']);
    }

    public function test_the_promo_stacks_with_the_advance_month_discount(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.update'), $this->payload());

        // 6 months of Professional: 299 x 6, less 15% advance, less 25% promo.
        $quote = SubscriptionPlans::quote('professional', 6);
        $expected = (int) round(29900 * 6 * 0.85 * 0.75);

        $this->assertSame(15, $quote['discount']);
        $this->assertSame(25, $quote['promo']);
        $this->assertSame($expected, $quote['total']);
    }

    public function test_a_plan_without_its_own_discount_falls_back_to_the_site_wide_one(): void
    {
        // A previously configured global promo must keep working.
        Setting::put(Setting::DISCOUNT, 30);

        $this->assertSame(30, SubscriptionPlans::promoPercent('starter'));

        // ...until a per-plan value replaces it.
        Setting::put(SubscriptionPlans::discountSettingKey('starter'), 5);
        $this->assertSame(5, SubscriptionPlans::promoPercent('starter'));
    }

    public function test_pricing_is_validated_and_admin_only(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->payload(['discounts' => ['professional' => 150]]))
            ->assertSessionHasErrors('discounts.professional');

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->payload(['prices' => ['starter' => 0]]))
            ->assertSessionHasErrors('prices.starter');

        // A teacher must never be able to reprice the product.
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
            'trial_ends_at' => now()->addDays(5),
        ]);

        $this->actingAs($teacher)->put(route('admin.settings.update'), $this->payload())->assertForbidden();
        $this->assertSame(19900, SubscriptionPlans::monthlyPrice('starter'));
    }
}
