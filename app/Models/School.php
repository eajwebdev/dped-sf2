<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    /** Teachers may only register into an active school. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Public URL of the uploaded school logo, or null (usable on reports/forms). */
    public function logoUrl(): ?string
    {
        return $this->logo_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->logo_path)
            : null;
    }
}
