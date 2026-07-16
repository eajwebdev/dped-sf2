<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'grade_level_id',
        'units',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class);
    }
}
