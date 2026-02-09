<?php

namespace App\Repositories;

use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    /**
     * Search users based on the provided search term.
     *
     * @return Collection<int, User>
     */
    public function search(array $searchQuery, array $relations = []): Collection
    {
        $term = $searchQuery['term'] ?? null;
        $role = $searchQuery['role'] ?? null;

        return User::with($relations)
            ->when($role, fn ($query) => $query->where('role', $role))
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Create a user (DB only - Supabase should be created beforehand).
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update a user (DB only - Supabase sync handled in service).
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    /**
     * Get a user by ID with specified relations.
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Get paginated list of users with specified relations.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, User>
     */
    public function getPaginatedByRole(?UserRole $role, int $perPage): LengthAwarePaginator
    {
        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Update user details (DB only).
     */
    public function updateUser(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    /**
     * Delete a user (DB only - Supabase deletion handled in service).
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    /**
     * Get all users by organization ID.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function getUsersByOrganizationID(int $organizationID, int $perPage): LengthAwarePaginator
    {
        $query = User::where('organization_id', $organizationID);

        return $query->latest()->paginate($perPage);
    }

    public function getAdminByOrganizationID(int $organizationID): User
    {
        return User::where('organization_id', $organizationID)
            ->where('role', UserRole::ADMIN->value)
            ->first();
    }

    public function assignRoleToUser(User $user, array $roleData): void
    {
        $user->assignRole($roleData['role_id']);
    }

    /**
     * Assign granular permissions to a user.
     */
    public function assignGranularPermissionsToUser(User $user, array $permissionIds): User
    {
        $user->givePermissionTo($permissionIds);

        return $user->fresh();
    }

    /**
     * Remove a role from a user.
     */
    public function removeRoleFromUser(User $user, Role $role): User
    {
        $user->removeRole($role);

        return $user->fresh();
    }

    /**
     * Revoke granular permissions from a user.
     */
    public function revokeGranularPermissionsFromUser(User $user, array $permissionIds): User
    {
        foreach ($permissionIds as $permissionId) {
            $user->revokePermissionTo($permissionId);
        }

        return $user->fresh();
    }
}
