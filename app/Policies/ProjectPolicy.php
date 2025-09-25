<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Enums\UserProjectRole;

class ProjectPolicy
{
    /**
     * Determine if the given user can add a member to the project.
     * Only the project owner can add members.
     */
    public function addMember(User $user, Project $project): bool
    {
        return $project->users()
            ->wherePivot('role', UserProjectRole::OWNER->value)
            ->whereKey($user->getKey())
            ->exists();
    }
}
