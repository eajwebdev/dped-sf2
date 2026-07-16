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

    protected $fillable = [
        'school_id',
        'name',
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

    /** Public URL of the uploaded school logo, or null (usable on reports/forms). */
    public function logoUrl(): ?string
    {
        // Logos live directly under public/ — no storage:link involved.
        return $this->logo_path ? asset($this->logo_path) : null;
    }
}
