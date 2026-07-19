<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    /** Abandoned at the gateway — started, but never completed or charged. */
    public const STATUS_CANCELLED = 'cancelled';

    /** Every outcome a started transaction can end in. */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
    ];

    /** A fresh term: extends the end date by the months bought. */
    public const KIND_PURCHASE = 'purchase';

    /** A tier change for months already paid for: moves the plan, not the date. */
    public const KIND_UPGRADE = 'upgrade';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_reference',
        'plan',
        'kind',
        'previous_plan',
        'months',
        'amount',
        'discount_percent',
        'currency',
        'status',
        'period_start',
        'period_end',
        'paid_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'paid_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Peso amount for display (centavos -> ₱). */
    public function pesoAmount(): float
    {
        return $this->amount / 100;
    }

    public function isUpgrade(): bool
    {
        return $this->kind === self::KIND_UPGRADE;
    }
}
