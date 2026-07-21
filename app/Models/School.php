<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Education levels a school may offer. Elementary is a separate school from
     * high school; Junior and Senior High can share one campus, hence the
     * combined value. These four cover every valid combination.
     */
    public const LEVEL_ES = 'es';

    public const LEVEL_JHS = 'jhs';

    public const LEVEL_SHS = 'shs';

    public const LEVEL_JHS_SHS = 'jhs_shs';

    /** value => human label, in display order. */
    public const LEVELS = [
        self::LEVEL_ES => 'Elementary (ES)',
        self::LEVEL_JHS => 'Junior High School (JHS)',
        self::LEVEL_SHS => 'Senior High School (SHS)',
        self::LEVEL_JHS_SHS => 'Junior & Senior High School (JHS + SHS)',
    ];

    /** Whether this school offers elementary grades. */
    public function offersEs(): bool
    {
        return $this->education_level === self::LEVEL_ES;
    }

    /** Whether this school offers Junior High (standalone or combined). */
    public function offersJhs(): bool
    {
        return in_array($this->education_level, [self::LEVEL_JHS, self::LEVEL_JHS_SHS], true);
    }

    /** Whether this school offers Senior High (standalone or combined). */
    public function offersShs(): bool
    {
        return in_array($this->education_level, [self::LEVEL_SHS, self::LEVEL_JHS_SHS], true);
    }

    /**
     * A single campus that runs both Junior AND Senior High — the case where a
     * form like SF9 must offer a JHS-or-SHS choice when generating.
     */
    public function offersBothHighSchool(): bool
    {
        return $this->education_level === self::LEVEL_JHS_SHS;
    }

    /** Memoised result of soleId(); false means "not looked up yet". */
    private static bool|int|null $soleId = false;

    /**
     * The id of the only school on this installation, or null when there is
     * not exactly one.
     *
     * Used to attribute rows created without a signed-in user (seeders,
     * console commands). It sits on the write path, so the answer is resolved
     * once per process; long-lived workers and tests must call forgetSoleId().
     */
    public static function soleId(): ?int
    {
        if (self::$soleId === false) {
            $ids = static::query()->limit(2)->pluck('id');
            self::$soleId = $ids->count() === 1 ? (int) $ids->first() : null;
        }

        return self::$soleId;
    }

    public static function forgetSoleId(): void
    {
        self::$soleId = false;
    }

    protected $fillable = [
        'school_id',
        'name',
        'education_level',
        'division',
        'region',
        'address',
        'logo_path',
        'is_active',
        'active_school_year_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** All user accounts (teachers) that joined this school. */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** This school's own active year; NULL follows the global active year. */
    public function activeSchoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'active_school_year_id');
    }

    /** Teachers may only register into an active school. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Human-readable education level (e.g. "Junior High School (JHS)"), or null. */
    public function educationLevelLabel(): ?string
    {
        return self::LEVELS[$this->education_level] ?? null;
    }

    /** Public URL of the uploaded school logo, or null (usable on reports/forms). */
    public function logoUrl(): ?string
    {
        // Logos live directly under public/ — no storage:link involved.
        return $this->logo_path ? asset($this->logo_path) : null;
    }
}
