<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'level_order',
        'is_graduating',
    ];

    protected function casts(): array
    {
        return [
            'level_order' => 'integer',
            'is_graduating' => 'boolean',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /** The next grade in promotion order, if any. */
    public function nextGrade(): ?self
    {
        return static::query()
            ->where('level_order', '>', $this->level_order)
            ->orderBy('level_order')
            ->first();
    }
}
