<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Settings sit on the hot path of every page, so they are cached twice: once
 * per request in memory, once in the cache store. Both layers have to stay
 * correct — a stale price is worse than a slow one.
 */
class SettingCachingTest extends TestCase
{
    use RefreshDatabase;

    /** Count the queries a callback actually runs. */
    private function queriesDuring(callable $fn): int
    {
        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $fn();

        return $count;
    }

    public function test_a_repeated_read_never_queries_twice(): void
    {
        Setting::put(Setting::PRICE, 29900);
        Setting::flushMemo();

        // First read may query; the next fifty must not.
        Setting::get(Setting::PRICE);

        $queries = $this->queriesDuring(function () {
            for ($i = 0; $i < 50; $i++) {
                Setting::get(Setting::PRICE);
            }
        });

        $this->assertSame(0, $queries, 'Repeated reads of one setting must be served from memory.');
    }

    public function test_a_missing_setting_is_cached_too(): void
    {
        /*
         * The regression this guards: Cache::rememberForever treats a stored
         * null as a miss, so an unset setting was re-queried AND re-written on
         * every read. Most settings are unset, so this dominated page load.
         */
        Setting::get('never_set_key');
        Setting::flushMemo();

        $queries = $this->queriesDuring(function () {
            for ($i = 0; $i < 10; $i++) {
                Setting::get('never_set_key');
                Setting::flushMemo();   // force it past the in-memory layer
            }
        });

        $this->assertSame(0, $queries, 'An absent setting must be cached, not re-queried each time.');
    }

    public function test_a_missing_setting_still_returns_its_default(): void
    {
        $this->assertSame('fallback', Setting::get('never_set_key', 'fallback'));
        $this->assertNull(Setting::get('never_set_key'));

        // And again, now that the sentinel is cached.
        $this->assertSame('fallback', Setting::get('never_set_key', 'fallback'));
    }

    public function test_writing_a_setting_invalidates_both_layers(): void
    {
        Setting::put(Setting::PRICE, 10000);
        $this->assertSame('10000', Setting::get(Setting::PRICE));

        Setting::put(Setting::PRICE, 25000);

        // No flush: the in-request memo must not survive its own write.
        $this->assertSame('25000', Setting::get(Setting::PRICE));

        Setting::flushMemo();
        $this->assertSame('25000', Setting::get(Setting::PRICE), 'The cache store must be invalidated too.');
    }

    public function test_a_price_change_is_visible_immediately_not_after_a_cache_expiry(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'status' => User::STATUS_APPROVED,
        ]);

        // Warm every layer by rendering the public page first.
        $this->get(route('landing'))->assertOk();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'module' => 'pricing',
            'prices' => ['starter' => 149, 'professional' => 299, 'enterprise' => 599],
            'discounts' => ['starter' => 0, 'professional' => 0, 'enterprise' => 0],
        ]);

        auth()->logout();

        $this->get(route('landing'))->assertOk()->assertSee('₱149', false);
        $this->assertSame(14900, SubscriptionPlans::monthlyPrice('starter'));
    }

    public function test_the_landing_page_does_not_query_per_plan_per_month(): void
    {
        // Warm the cache, then measure a realistic repeat visit.
        $this->get(route('landing'))->assertOk();

        $queries = $this->queriesDuring(function () {
            Setting::flushMemo();               // a fresh request
            $this->get(route('landing'))->assertOk();
        });

        /*
         * The page prices 3 tiers across 12 month-options. Before caching that
         * was 60 queries; anything near that number means the cache is being
         * bypassed again.
         */
        $this->assertLessThan(10, $queries, "Landing page ran {$queries} queries — caching has regressed.");
    }
}
