<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClassSession extends Model
{
    use BelongsToSchool, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ENDED = 'ended';

    protected $fillable = [
        'school_id',
        'teacher_id',
        'section_id',
        'subject_id',
        'school_year_id',
        'teacher_schedule_id',
        'session_date',
        'qr_key',
        'status',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** A short, human-typable key that is unique among active sessions. */
    public static function generateKey(): string
    {
        do {
            $key = strtoupper(Str::random(6));
        } while (static::withoutGlobalScopes()->where('qr_key', $key)->where('status', self::STATUS_ACTIVE)->exists());

        return $key;
    }
}
