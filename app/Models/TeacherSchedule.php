<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class TeacherSchedule extends Model
{
    use HasFactory, SoftDeletes;

    public const DAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    public const COLORS = ['indigo', 'emerald', 'amber', 'rose', 'sky', 'violet'];

    protected $fillable = [
        'teacher_id',
        'school_year_id',
        'section_id',
        'subject_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'color',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** Entries for the active school year. */
    public function scopeActiveYear(Builder $query): Builder
    {
        return $query->whereHas('schoolYear', fn (Builder $q) => $q->where('is_active', true));
    }

    /** Entries covering the given moment (same ISO weekday, time between start and end). */
    public function scopeHappeningAt(Builder $query, Carbon $moment): Builder
    {
        return $query->where('day_of_week', $moment->isoWeekday())
            ->where('start_time', '<=', $moment->format('H:i:s'))
            ->where('end_time', '>', $moment->format('H:i:s'));
    }

    /** True when this entry overlaps [start, end) on the same day for the same teacher. */
    public static function overlaps(int $teacherId, int $schoolYearId, int $day, string $start, string $end, ?int $ignoreId = null): bool
    {
        return static::query()
            ->where('teacher_id', $teacherId)
            ->where('school_year_id', $schoolYearId)
            ->where('day_of_week', $day)
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();
    }

    protected function dayName(): Attribute
    {
        return Attribute::make(get: fn () => self::DAYS[$this->day_of_week] ?? '—');
    }

    protected function timeRange(): Attribute
    {
        return Attribute::make(get: fn () => sprintf(
            '%s – %s',
            Carbon::parse($this->start_time)->format('g:i A'),
            Carbon::parse($this->end_time)->format('g:i A'),
        ));
    }
}
