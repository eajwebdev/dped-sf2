<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEnrollment extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ENROLLED = 'enrolled';

    public const STATUS_TRANSFERRED_IN = 'transferred_in';

    public const STATUS_TRANSFERRED_OUT = 'transferred_out';

    public const STATUS_DROPPED = 'dropped';

    public const STATUS_PROMOTED = 'promoted';

    public const STATUS_RETAINED = 'retained';

    public const STATUS_GRADUATED = 'graduated';

    protected $fillable = [
        'student_id',
        'school_year_id',
        'grade_level_id',
        'section_id',
        'status',
        'promotion_status',
        'enrollment_date',
        'is_late_enrollment',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
            'is_late_enrollment' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_ENROLLED, self::STATUS_TRANSFERRED_IN], true);
    }
}
