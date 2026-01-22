<?php

namespace App\Listeners;

use Spatie\Permission\Models\Role;
use App\Events\OrganizationAdminCreated;
use Spatie\Permission\Models\Permission;

class SetupPredefinedRolesForOrganization
{
    /**
     * Handle the event.
     */
    public function handle(OrganizationAdminCreated $event): void
    {
        $admin = $event->admin;
        $organization = $event->organization;

        setPermissionsTeamId($organization->id);
        $role = Role::firstOrCreate(
            [
                'name' => 'Admin',
                'guard_name' => 'supabase',
                'team_id' => $organization->id,
            ]
        );
        // Assign all permissions to the Admin role
        $permissions = Permission::all();
        $role->syncPermissions($permissions);
        $admin->assignRole($role);
    }
}
