<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolCalendar extends Model
{
    use HasFactory;

    protected $table = 'school_calendar';

    protected $fillable = [
        'school_year_id',
        'date',
        'day_type',
        'is_class_day',
        'is_override',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_class_day' => 'boolean',
            'is_override' => 'boolean',
        ];
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
