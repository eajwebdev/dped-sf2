<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolYear extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class);
    }

    public function calendarDays(): HasMany
    {
        return $this->hasMany(SchoolCalendar::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    public function attendanceSetting(): HasOne
    {
        return $this->hasOne(AttendanceSetting::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static ?self $cachedCurrent = null;

    protected static bool $currentResolved = false;

    /** Resolve the single active school year (memoized per request). */
    /**
     * The active school year for a specific user: their school's own setting
     * when one exists, otherwise the global active year. A sudden change by
     * the admin takes effect on the user's next request through this path.
     */
    public static function activeFor(?User $user): ?self
    {
        $override = $user?->school_id
            ? School::query()->whereKey($user->school_id)->first()?->activeSchoolYear
            : null;

        return $override ?? static::current();
    }

    public static function current(): ?self
    {
        if (! static::$currentResolved) {
            static::$cachedCurrent = static::query()->where('is_active', true)->first();
            static::$currentResolved = true;
        }

        return static::$cachedCurrent;
    }

    /** Clear the memoized active year (call after changing which year is active). */
    public static function forgetCurrent(): void
    {
        static::$cachedCurrent = null;
        static::$currentResolved = false;
    }
}
