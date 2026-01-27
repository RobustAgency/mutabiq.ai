<?php

namespace App\Repositories;

use Spatie\Permission\Models\Permission;

class PermissionRepository
{
    /**
     * Get all available permissions.
     */
    public function getAllPermissions()
    {
        $permissions = Permission::all()->groupBy([
            fn ($p) => explode('.', $p->name)[0], // Module
            fn ($p) => explode('.', $p->name)[1], // Screen
        ]);

        return $permissions;
    }
}
