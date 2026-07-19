<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A title the adviser hands out to a section, tracked on SF3. */
class Textbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'subject_area',
        'title',
        'sort',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function issuances(): HasMany
    {
        return $this->hasMany(TextbookIssuance::class);
    }
}
