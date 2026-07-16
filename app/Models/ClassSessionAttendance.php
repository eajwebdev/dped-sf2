<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One learner's presence in one class session (period). Absence is represented
 * by the absence of a row, so a learner's skipped periods are the sessions that
 * ran for their section today without a matching row here.
 */
class ClassSessionAttendance extends Model
{
    protected $table = 'class_session_attendance';

    public const STATUS_PRESENT = 'present';

    public const STATUS_LATE = 'late';

    protected $fillable = [
        'class_session_id',
        'student_id',
        'student_enrollment_id',
        'status',
        'time_in',
    ];

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
