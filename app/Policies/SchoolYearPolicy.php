<?php

namespace App\Policies;

use App\Models\SchoolYear;
use App\Models\User;

class SchoolYearPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, SchoolYear $schoolYear): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, SchoolYear $schoolYear): bool
    {
        return $user->isAdmin() && $schoolYear->status !== SchoolYear::STATUS_ARCHIVED;
    }

    public function delete(User $user, SchoolYear $schoolYear): bool
    {
        // Never delete a year that already holds enrollments; archive instead.
        return $user->isAdmin() && ! $schoolYear->enrollments()->exists();
    }
}
