<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const PRICE = 'subscription_price_centavos';

    public const DISCOUNT = 'subscription_discount_percent';

    public const PAYMONGO_SECRET = 'paymongo_secret_key';

    public const PAYMONGO_PUBLIC = 'paymongo_public_key';

    public const PAYMONGO_WEBHOOK = 'paymongo_webhook_secret';

    protected $fillable = ['key', 'value'];

    /**
     * Values already resolved during this request.
     *
     * Settings are read many times per page — pricing alone touches them once
     * per plan per month option — and without this every read is a round trip
     * to the cache store. Static state is safe under PHP-FPM, where each
     * request gets a fresh process; long-lived workers (Octane, queues) must
     * call flushMemo() between jobs.
     *
     * @var array<string, mixed>
     */
    private static array $memo = [];

    /**
     * Marker for "this setting genuinely has no value".
     *
     * Cache::rememberForever() treats a stored null as a miss, so a setting
     * that is not in the database would be re-queried and re-written on every
     * single read. Caching a sentinel instead makes an absent setting as cheap
     * to look up as a present one.
     */
    private const NONE = '__setting_none__';

    /** Read a setting, falling back to the given default when unset. */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (! array_key_exists($key, self::$memo)) {
            self::$memo[$key] = Cache::rememberForever(
                "setting:{$key}",
                fn () => static::query()->where('key', $key)->value('value') ?? self::NONE,
            );
        }

        $value = self::$memo[$key];

        return $value === self::NONE || $value === null ? $default : $value;
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);

        // Both layers must go, and the memo first: a stale in-request value
        // would otherwise survive the write that was meant to replace it.
        unset(self::$memo[$key]);
        Cache::forget("setting:{$key}");
    }

    /** Drop request-local state. Long-lived workers must call this per job. */
    public static function flushMemo(): void
    {
        self::$memo = [];
    }

    /** List price in centavos, before any discount. */
    public static function priceCentavos(): int
    {
        return (int) static::get(self::PRICE, config('services.paymongo.price', 29900));
    }

    /** Whole-percent discount applied to the list price (0–100). */
    public static function discountPercent(): int
    {
        return (int) static::get(self::DISCOUNT, 0);
    }

    /** What a subscriber actually pays, in centavos. */
    public static function effectivePriceCentavos(): int
    {
        $price = static::priceCentavos();
        $discount = max(0, min(100, static::discountPercent()));

        return (int) round($price * (100 - $discount) / 100);
    }

    /** PayMongo credentials: admin-set value first, .env as fallback. */
    public static function paymongoSecretKey(): ?string
    {
        return static::get(self::PAYMONGO_SECRET, config('services.paymongo.secret_key')) ?: null;
    }

    public static function paymongoPublicKey(): ?string
    {
        return static::get(self::PAYMONGO_PUBLIC, config('services.paymongo.public_key')) ?: null;
    }

    public static function paymongoWebhookSecret(): ?string
    {
        return static::get(self::PAYMONGO_WEBHOOK, config('services.paymongo.webhook_secret')) ?: null;
    }
}
