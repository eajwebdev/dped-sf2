<?php

namespace App\Models\Concerns;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes a model to the acting user's school (school-shared multi-tenancy).
 *
 * The global scope is intentionally lenient: it only filters for an
 * authenticated, non-admin user who has a school_id. Admins are unscoped,
 * and console/tests/legacy accounts without a school see everything — so
 * existing single-tenant data and flows keep working. New rows created by a
 * scoped user are automatically stamped with that user's school_id.
 */
trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            if ($schoolId = static::currentSchoolId()) {
                $builder->where($builder->getModel()->getTable().'.school_id', $schoolId);
            }
        });

        static::creating(function ($model) {
            if ($model->school_id === null && ($schoolId = static::currentSchoolId())) {
                $model->school_id = $schoolId;
            }
        });
    }

    /** The school_id to scope by, or null when the caller should be unscoped. */
    protected static function currentSchoolId(): ?int
    {
        $user = auth()->user();

        if (! $user || $user->isAdmin() || ! $user->school_id) {
            return null;
        }

        return $user->school_id;
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
