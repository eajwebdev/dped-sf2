<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year_id',
        'edit_lock_days',
        'autosave_seconds',
        'block_future_dates',
        'allow_holiday_override',
        'count_late_as_present',
    ];

    protected function casts(): array
    {
        return [
            'edit_lock_days' => 'integer',
            'autosave_seconds' => 'integer',
            'block_future_dates' => 'boolean',
            'allow_holiday_override' => 'boolean',
            'count_late_as_present' => 'boolean',
        ];
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /**
     * Effective settings for a school year: the year-specific row if present,
     * otherwise the global default row (school_year_id = null).
     */
    public static function resolve(?int $schoolYearId = null): self
    {
        if ($schoolYearId) {
            $specific = static::where('school_year_id', $schoolYearId)->first();
            if ($specific) {
                return $specific;
            }
        }

        return static::firstOrCreate(['school_year_id' => null]);
    }
}
