<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEnrollment extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    public const STATUS_ENROLLED = 'enrolled';

    public const STATUS_TRANSFERRED_IN = 'transferred_in';

    public const STATUS_TRANSFERRED_OUT = 'transferred_out';

    public const STATUS_DROPPED = 'dropped';

    public const STATUS_PROMOTED = 'promoted';

    public const STATUS_RETAINED = 'retained';

    public const STATUS_GRADUATED = 'graduated';

    protected $fillable = [
        'school_id',
        'student_id',
        'school_year_id',
        'grade_level_id',
        'section_id',
        'status',
        'promotion_status',
        'general_average',
        'is_irregular',
        'subjects_completed',
        'subjects_incomplete',
        'enrollment_date',
        'is_late_enrollment',
        'remarks',

        // SF1 REMARKS indicators (see Sf1ReportService::INDICATORS).
        'transfer_school',
        'transfer_date',
        'dropped_reason',
        'dropped_date',
        'late_enrollment_reason',
        'cct_reference',
        'balik_aral_detail',
        'disability_detail',
        'accelerated_detail',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
            'is_late_enrollment' => 'boolean',
            'general_average' => 'decimal:3',
            'is_irregular' => 'boolean',
            'transfer_date' => 'date',
            'dropped_date' => 'date',
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

    public function textbookIssuances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TextbookIssuance::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(LearnerGrade::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(LearnerValue::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_ENROLLED, self::STATUS_TRANSFERRED_IN], true);
    }
}
