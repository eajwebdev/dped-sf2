<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    public const STATUS_PRESENT = 'present';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_LATE = 'late';

    public const STATUS_EXCUSED = 'excused';

    public const STATUS_HALF_DAY = 'half_day';

    public const STATUS_NO_CLASS = 'no_class';

    protected $fillable = [
        'student_enrollment_id',
        'student_id',
        'section_id',
        'school_year_id',
        'subject_assignment_id',
        'attendance_date',
        'status',
        'time_in',
        'is_locked',
        'remarks',
        'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'is_locked' => 'boolean',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function subjectAssignment(): BelongsTo
    {
        return $this->belongsTo(SubjectAssignment::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /** Statuses that count as the learner being present for daily-attendance totals. */
    public static function presentStatuses(bool $countLate = true): array
    {
        $statuses = [self::STATUS_PRESENT, self::STATUS_HALF_DAY];
        if ($countLate) {
            $statuses[] = self::STATUS_LATE;
        }

        return $statuses;
    }
}
