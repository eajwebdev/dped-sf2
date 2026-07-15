<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return $user->isAdmin() || $user->id === $teacher->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->isAdmin()
            && ! $teacher->advisedSections()->exists()
            && ! $teacher->subjectAssignments()->exists();
    }
}
