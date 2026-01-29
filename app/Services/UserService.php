<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Clients\SupabaseClient;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private SupabaseClient $supabaseClient,
        private UserRepository $userRepository,
    ) {}

    /**
     * Create a user with a specific role (handles both Supabase and DB operations).
     */
    public function createUserWithRoleAndOrganization(array $userData, UserRole $role, int $organizationId): User
    {
        $supabaseUser = $this->supabaseClient->createUser([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => $userData['password'],
            'role' => $role->value,
        ]);

        return $this->userRepository->create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => bcrypt($userData['password']),
            'role' => $role->value,
            'supabase_id' => $supabaseUser['id'],
            'organization_id' => $organizationId,
        ]);
    }

    /**
     * Create an admin for a specific organization.
     */
    public function createAdminForOrganization(array $adminData, int $organizationId): User
    {
        $admin = $this->createUserWithRoleAndOrganization($adminData, UserRole::ADMIN, $organizationId);

        return $admin;
    }

    /**
     * Create a regular user for a specific organization.
     */
    public function createUserForOrganization(array $userData, int $organizationId): User
    {
        $user = $this->createUserWithRoleAndOrganization($userData, UserRole::USER, $organizationId);

        return $user;
    }
}
