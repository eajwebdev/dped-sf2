<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherSubjectAssignment extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'subject_assignment_id',
        'teacher_id',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function subjectAssignment(): BelongsTo
    {
        return $this->belongsTo(SubjectAssignment::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
