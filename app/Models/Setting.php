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

    /** Read a setting, falling back to the given default when unset. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::rememberForever("setting:{$key}", fn () => static::query()->where('key', $key)->value('value'));

        return $value ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("setting:{$key}");
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
