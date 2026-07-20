<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearnerGrade extends Model
{
    use BelongsToSchool;

    /** SF9 uses four grading periods (JHS quarters, or SHS semester-quarters). */
    public const PERIODS = [1, 2, 3, 4];

    protected $fillable = [
        'school_id',
        'student_enrollment_id',
        'subject_id',
        'period',
        'grade',
    ];

    protected function casts(): array
    {
        return [
            'period' => 'integer',
            'grade' => 'decimal:2',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
