<?php

namespace App\Models\Concerns;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes a model to the acting user's school (school-shared multi-tenancy).
 *
 * The scope fails CLOSED. A signed-in, non-admin user without a school_id
 * matches nothing at all, rather than every school in the database — one
 * missing column on one account must never expose every other tenant's
 * learners. Two callers are deliberately unscoped: admins, who administer
 * across schools, and unauthenticated code (console commands, seeders,
 * scheduled jobs), which has no user to scope by.
 *
 * Rows created by a scoped user are stamped with that user's school_id.
 */
trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            $user = auth()->user();

            // No user (console, seeders, jobs) or an admin: no filter.
            if (! $user || $user->isAdmin()) {
                return;
            }

            $table = $builder->getModel()->getTable();

            if ($user->school_id === null) {
                // Fail closed: an unassigned account is not a superuser.
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where($table.'.school_id', $user->school_id);
        });

        static::creating(function ($model) {
            if ($model->school_id !== null) {
                return;
            }

            if ($schoolId = static::currentSchoolId()) {
                $model->school_id = $schoolId;

                return;
            }

            /*
             * Created with no signed-in user — a seeder, console command or
             * scheduled job. When the installation holds exactly one school
             * the owner is unambiguous, so stamp it rather than leave an
             * orphan row that no scoped user can ever read. With none, or
             * several, guessing would be worse than leaving it null.
             */
            if (! auth()->check() && ($only = static::soleSchoolId()) !== null) {
                $model->school_id = $only;
            }
        });
    }

    /** The school_id to stamp on new rows, or null when there is nothing to stamp. */
    protected static function currentSchoolId(): ?int
    {
        $user = auth()->user();

        if (! $user || $user->isAdmin() || ! $user->school_id) {
            return null;
        }

        return $user->school_id;
    }

    /** @see School::soleId() — memoised there so it can be reset per test. */
    protected static function soleSchoolId(): ?int
    {
        return School::soleId();
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
