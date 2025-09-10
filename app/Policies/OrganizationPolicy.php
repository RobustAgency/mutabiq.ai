<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;

class OrganizationPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::OWNER;
    }
}
