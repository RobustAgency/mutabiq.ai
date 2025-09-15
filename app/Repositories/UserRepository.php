<?php

namespace App\Repositories;

use App\Models\User;
use App\Enums\UserRole;
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
     * Create an Admin user.
     */
    public function createAdmin(array $adminData): User
    {
        return User::create([
            'name' => $adminData['name'],
            'email' => $adminData['email'],
            'password' => bcrypt($adminData['password']),
            'role' => UserRole::ADMIN,
            'supabase_id' => $adminData['supabase_id'],
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
}
