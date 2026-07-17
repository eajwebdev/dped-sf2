<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Student extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'lrn',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'birthdate',
        'address',
        'guardian_name',
        'guardian_contact',
        'status',
        'photo_path',
        'qr_token',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Student $student) {
            $student->qr_token ??= (string) Str::uuid();
        });
    }

    /**
     * Return the QR token, generating and persisting one for legacy rows
     * created before tokens were auto-assigned.
     */
    public function ensureQrToken(): string
    {
        if (blank($this->qr_token)) {
            $this->forceFill(['qr_token' => (string) Str::uuid()])->saveQuietly();
        }

        return $this->qr_token;
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** The learner's enrollment in the currently active school year, if any. */
    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class)
            ->whereHas('schoolYear', fn ($q) => $q->where('is_active', true));
    }

    /** DepEd display format: "Last Name, First Name Middle Initial". */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(sprintf(
                '%s, %s %s%s',
                $this->last_name,
                $this->first_name,
                $this->middle_name ? substr($this->middle_name, 0, 1).'.' : '',
                $this->suffix ? ' '.$this->suffix : ''
            )),
        );
    }
}
