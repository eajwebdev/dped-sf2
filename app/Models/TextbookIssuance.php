<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One learner's copy of one textbook: when it went out, when it came back. */
class TextbookIssuance extends Model
{
    use HasFactory;

    /**
     * Codes for a copy that did not come back, printed in the Date Returned
     * cell as the official form directs.
     */
    public const RETURN_CODES = [
        'FM' => 'Force Majeure',
        'TDO' => 'Transferred / Dropped Out',
        'NEG' => 'Negligence',
    ];

    /** Action-taken codes for the REMARKS column, paired with the return code. */
    public const ACTION_CODES = [
        'LLTR' => 'Letter from learner, signed by parent/guardian (for FM)',
        'TLTR' => 'Teacher letter noted by School Head, for the Property Custodian (for TDO)',
        'PTL' => 'Paid by the learner (for NEG)',
    ];

    protected $fillable = [
        'textbook_id',
        'student_enrollment_id',
        'issued_at',
        'returned_at',
        'return_code',
        'action_code',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'returned_at' => 'date',
        ];
    }

    public function textbook(): BelongsTo
    {
        return $this->belongsTo(Textbook::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }
}
