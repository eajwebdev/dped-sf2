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

    /**
     * The official DepEd behaviour statements marked under each core value on
     * the SF9. The 1-based position in each list is the `behavior` index stored
     * against a mark. Source: DepEd K to 12 Report Card (SF9) core-values rubric.
     *
     * @var array<string, array<int, string>>
     */
    public const BEHAVIORS = [
        'maka_diyos' => [
            'Expresses one\'s spiritual beliefs while respecting the spiritual beliefs of others.',
            'Shows adherence to ethical principles by upholding truth in all undertakings.',
        ],
        'makatao' => [
            'Is sensitive to individual, social, and cultural differences.',
            'Demonstrates contributions towards solidarity.',
        ],
        'maka_kalikasan' => [
            'Cares for environment and utilizes resources wisely, judiciously, and economically.',
        ],
        'maka_bansa' => [
            'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.',
            'Demonstrates appropriate behavior in carrying out activities in the school, community, and country.',
        ],
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
        'behavior',
        'period',
        'mark',
    ];

    protected function casts(): array
    {
        return [
            'behavior' => 'integer',
            'period' => 'integer',
        ];
    }

    /** Number of official behaviour statements for a core value (0 if unknown). */
    public static function behaviorCount(string $coreValue): int
    {
        return count(self::BEHAVIORS[$coreValue] ?? []);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }
}
