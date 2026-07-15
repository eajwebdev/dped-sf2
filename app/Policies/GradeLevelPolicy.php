<?php

namespace App\Policies;

use App\Models\GradeLevel;
use App\Models\User;

class GradeLevelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->isAdmin()
            && ! $gradeLevel->sections()->exists()
            && ! $gradeLevel->enrollments()->exists();
    }
}
