<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Section $section): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // A teacher may view a section they advise or teach a subject in.
        $teacherId = $user->teacher?->id;

        return $teacherId && (
            $section->adviser_id === $teacherId
            || $section->subjectAssignments()
                ->whereHas('teacherAssignments', fn ($q) => $q->where('teacher_id', $teacherId))
                ->exists()
        );
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Section $section): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Section $section): bool
    {
        return $user->isAdmin() && ! $section->enrollments()->exists();
    }
}
