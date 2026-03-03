<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleRepository
{
    /**
     * Get all roles.
     *
     * @return LengthAwarePaginator<int, Role>
     */
    public function getFilteredRoles(array $filters): LengthAwarePaginator
    {
        $teamId = getPermissionsTeamId();

        $query = Role::with('permissions')
            ->where('team_id', $teamId);

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function createRole(array $roleData): Role
    {
        $teamId = getPermissionsTeamId();

        $role = Role::query()->create([
            'name' => $roleData['name'],
            'guard_name' => 'supabase',
            'team_id' => $teamId,
        ]);

        if (! empty($roleData['permissions'])) {
            $role->givePermissionTo($roleData['permissions']);
        }

        return $role->load('permissions');
    }

    public function updateRole(Role $role, array $roleData): Role
    {
        $role->name = $roleData['name'] ?? $role->name;
        $role->save();

        if (array_key_exists('permissions', $roleData)) {
            $role->syncPermissions($roleData['permissions'] ?? []);
        }

        return $role->load('permissions');
    }

    /**
     * Get a role by ID.
     */
    public function getRoleById(int $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    public function assignPermissionsToRole(Role $role, array $permissions): void
    {
        $role->givePermissionTo($permissions);
    }

    public function revokePermissionsFromRole(Role $role, array $permissions): void
    {
        foreach ($permissions as $permission) {
            $role->revokePermissionTo($permission);
        }
    }
}
