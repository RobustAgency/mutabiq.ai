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
        $pivot = $project->users()->where('user_id', $user->id)->first()?->pivot;

        return $pivot && $pivot->role === UserProjectRole::OWNER->value;
    }
}
