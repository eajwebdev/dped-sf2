<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearnerValue extends Model
{
    use BelongsToSchool;

    /** The four DepEd core values, value => display label. */
    public const CORE_VALUES = [
        'maka_diyos' => 'Maka-Diyos',
        'makatao' => 'Makatao',
        'maka_kalikasan' => 'Maka-Kalikasan',
        'maka_bansa' => 'Maka-Bansa',
    ];

    /** Non-numerical rating marks, value => label. */
    public const MARKS = [
        'AO' => 'Always Observed',
        'SO' => 'Sometimes Observed',
        'RO' => 'Rarely Observed',
        'NO' => 'Not Observed',
    ];

    protected $fillable = [
        'school_id',
        'student_enrollment_id',
        'core_value',
        'period',
        'mark',
    ];

    protected function casts(): array
    {
        return [
            'period' => 'integer',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }
}
