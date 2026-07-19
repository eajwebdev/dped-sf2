<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\SubscriptionPlans;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_TEACHER = 'teacher';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    /** Length of the free trial granted when an account is approved. */
    public const TRIAL_DAYS = 14;

    /** How close to expiry a subscriber may renew for another term. */
    public const RENEWAL_WINDOW_DAYS = 14;

    /**
     * Default attribute values. Accounts are approved by default (admins and
     * admin-provisioned teachers); public self-registration overrides this to
     * `pending` so those accounts await approval.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::STATUS_APPROVED,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'school_id',
        'school_id_number',
        'school_id_document_path',
        'status',
        'contact_number',
        'trial_ends_at',
        'subscribed_until',
        'subscription_plan',
        'free_access',
        'approved_at',
        'approved_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // Never let the storage path of an identity document leak into JSON.
        'school_id_document_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'trial_ends_at' => 'datetime',
            'subscribed_until' => 'date',
            'free_access' => 'boolean',
            'approved_at' => 'datetime',
            'school_id_verified_at' => 'datetime',
        ];
    }

    /** Whether an admin has confirmed this teacher's school ID document. */
    public function hasVerifiedSchoolId(): bool
    {
        return $this->school_id_verified_at !== null;
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /** Within the free-trial window granted at approval. */
    public function onTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /** Paid subscription still covers today. */
    public function isSubscribed(): bool
    {
        return $this->subscribed_until !== null
            && $this->subscribed_until->gte(Carbon::today());
    }

    /**
     * Whether this account has ever entered the billing funnel (self-registered
     * teachers get a trial at approval). Admin-provisioned teachers that were
     * never given a trial are "managed" and not subject to the paywall.
     */
    public function isBillingEnrolled(): bool
    {
        return $this->trial_ends_at !== null || $this->subscribed_until !== null;
    }

    /**
     * Whether this account may currently use the teacher app. Admins always
     * may; approved teachers pass if they are managed (never enrolled in
     * billing) or currently on trial / subscribed.
     */
    public function hasActiveAccess(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->isApproved()) {
            return false;
        }

        if ($this->free_access || ! $this->isBillingEnrolled()) {
            return true;
        }

        return $this->onTrial() || $this->isSubscribed();
    }

    /** Coarse state for badges and gating: pending|managed|trial|active|expired. */
    public function subscriptionState(): string
    {
        if (! $this->isApproved()) {
            return self::STATUS_PENDING;
        }

        // Owner-granted comp: overrides billing entirely while switched on.
        if ($this->free_access) {
            return 'free';
        }

        if (! $this->isBillingEnrolled()) {
            return 'managed';
        }

        if ($this->isSubscribed()) {
            return 'active';
        }

        if ($this->onTrial()) {
            return 'trial';
        }

        return 'expired';
    }

    /**
     * Extend the paid period by the given number of months, stacking onto any
     * remaining time (or starting from today when lapsed).
     */
    /**
     * Whole months left on a paid subscription, rounded up so a part-month is
     * never billed as nothing. Zero when not currently subscribed.
     */
    public function remainingSubscriptionMonths(): int
    {
        if (! $this->isSubscribed()) {
            return 0;
        }

        $today = Carbon::today();
        $months = (int) $today->diffInMonths($this->subscribed_until);

        // Round a partial month up: 3 months and 4 days bills as 4.
        if ($today->copy()->addMonthsNoOverflow($months)->lt($this->subscribed_until)) {
            $months++;
        }

        return max(1, $months);
    }

    /** The plan currently in force, defaulting to the entry tier. */
    public function currentPlan(): string
    {
        return SubscriptionPlans::exists($this->subscription_plan)
            ? $this->subscription_plan
            : SubscriptionPlans::STARTER;
    }

    /**
     * Renewing is only opened up near the end of the term, so a subscriber
     * cannot stack a second period on top of one they are barely into. Outside
     * that window an active subscriber's only move is an upgrade.
     */
    public function canRenew(): bool
    {
        if (! $this->isSubscribed()) {
            return true;
        }

        return Carbon::today()->diffInDays($this->subscribed_until, false) <= self::RENEWAL_WINDOW_DAYS;
    }

    /** Whether this account may move up to $plan right now. */
    public function canUpgradeTo(string $plan): bool
    {
        return $this->isSubscribed() && SubscriptionPlans::isUpgradeFrom($this->currentPlan(), $plan);
    }

    /**
     * Whether this account may use a gated School Form module (e.g. 'sf3').
     *
     * Admins, comped accounts, managed (never-billed) teachers and trial users
     * all get the full product — the plan gate only applies once someone is
     * actually paying for a specific tier.
     */
    public function hasModule(string $module): bool
    {
        if ($this->isAdmin() || $this->free_access || ! $this->isBillingEnrolled() || $this->onTrial()) {
            return true;
        }

        return SubscriptionPlans::planCovers($this->currentPlan(), $module);
    }

    public function extendSubscription(int $months = 1): Carbon
    {
        $base = $this->isSubscribed() ? $this->subscribed_until->copy() : Carbon::today();
        $newUntil = $base->addMonthsNoOverflow($months);

        $this->forceFill(['subscribed_until' => $newUntil])->save();

        return $newUntil;
    }

    /**
     * Query for sections this user can take attendance in: everything for an
     * admin, otherwise only the linked teacher's advised/taught sections.
     */
    public function accessibleSections(): Builder
    {
        $query = Section::query();

        if ($this->isAdmin()) {
            return $query;
        }

        $teacherId = $this->teacher?->id;

        // No linked teacher record -> no sections.
        return $teacherId ? $query->forTeacher($teacherId) : $query->whereRaw('1 = 0');
    }

    /**
     * Sections this user is the class adviser of — a strict subset of
     * accessibleSections(), which also includes sections merely taught.
     * SF2 is the adviser's form, so it must never widen past this.
     */
    public function advisorySections(): Builder
    {
        $query = Section::query();

        if ($this->isAdmin()) {
            return $query;
        }

        $teacherId = $this->teacher?->id;

        return $teacherId ? $query->where('adviser_id', $teacherId) : $query->whereRaw('1 = 0');
    }
}
