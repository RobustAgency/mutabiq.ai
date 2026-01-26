<?php

namespace App\Repositories;

use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Clients\SupabaseClient;
use Spatie\Permission\Models\Role;
use App\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function __construct(private SupabaseClient $supabaseClient) {}

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

    public function createAdmin(array $adminData): User
    {
        $supabaseUser = $this->supabaseClient->createUser([
            'name' => $adminData['name'],
            'email' => $adminData['email'],
            'password' => $adminData['password'],
            'role' => UserRole::ADMIN->value,
        ]);

        return User::create([
            'name' => $adminData['name'],
            'email' => $adminData['email'],
            'password' => bcrypt($adminData['password']),
            'role' => UserRole::ADMIN->value,
            'supabase_id' => $supabaseUser['id'],
        ]);
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
     * Update user details and sync with Supabase.
     */
    public function updateUser(User $user, array $data): UserResource
    {
        // This is because email is required by Supabase but optional in our update
        $data['email'] = $data['email'] ?? $user->email;

        $this->supabaseClient->updateUser($user->supabase_id, $data);

        $user->update($data);

        return new UserResource($user);
    }

    /**
     * Delete a user and remove from Supabase.
     */
    public function deleteUser(User $user): void
    {
        $this->supabaseClient->deleteUser($user->supabase_id);
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

    /**
     * Create an Admin user for a specific organization.
     */
    public function createAdminForOrganization(array $adminData, Organization $organization): User
    {
        $admin = $this->createAdmin($adminData);
        $admin->update([
            'organization_id' => $organization->id,
        ]);

        return $admin;
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
