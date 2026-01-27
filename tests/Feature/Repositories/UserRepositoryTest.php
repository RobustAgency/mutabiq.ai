<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use Tests\Fakes\FakeSupabase;
use Spatie\Permission\Models\Role;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = app(UserRepository::class);
    }

    public function test_it_get_users_by_organization_id(): void
    {
        $organization = Organization::factory()->create();
        $organizationID = $organization->id;

        User::factory()->count(3)->create(['organization_id' => $organizationID]);

        $users = $this->userRepository->getUsersByOrganizationID($organizationID, 10);

        $this->assertCount(3, $users);
        foreach ($users as $user) {
            $this->assertEquals($organizationID, $user->organization_id);
        }
    }

    public function test_create_admin_successfully(): void
    {
        Http::fake([
            '*/auth/v1/admin/users' => function ($request) {
                $requestData = $request->data();

                return Http::response(FakeSupabase::getUserCreationResponse([
                    'email' => $requestData['email'],
                    'name' => $requestData['user_metadata']['name'] ?? 'Test Admin',
                ]), 200);
            },
        ]);

        $adminData = [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ];

        $user = $this->userRepository->createAdmin($adminData);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'name' => 'Admin User',
            'role' => UserRole::ADMIN->value,
        ]);
        $this->assertEquals(UserRole::ADMIN, $user->role);
        $this->assertNotNull($user->supabase_id);
    }

    public function test_create_admin_for_organization(): void
    {
        Http::fake([
            '*/auth/v1/admin/users' => function ($request) {
                $requestData = $request->data();

                return Http::response(FakeSupabase::getUserCreationResponse([
                    'email' => $requestData['email'],
                    'name' => $requestData['user_metadata']['name'] ?? 'Test Admin',
                ]), 200);
            },
        ]);

        $organization = Organization::factory()->create();
        $adminData = [
            'name' => 'Organization Admin',
            'email' => 'org.admin@example.com',
            'password' => 'password123',
        ];

        $admin = $this->userRepository->createAdminForOrganization($adminData, $organization);

        $this->assertDatabaseHas('users', [
            'email' => 'org.admin@example.com',
            'name' => 'Organization Admin',
            'role' => UserRole::ADMIN->value,
            'organization_id' => $organization->id,
        ]);
        $this->assertEquals($organization->id, $admin->organization_id);
        $this->assertEquals(UserRole::ADMIN, $admin->role);
    }

    public function test_get_admin_by_organization_id(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => UserRole::ADMIN->value,
        ]);

        // Create other users to ensure correct filtering
        User::factory()->create([
            'organization_id' => $organization->id,
            'role' => UserRole::CONTRIBUTOR->value,
        ]);
        User::factory()->create([
            'role' => UserRole::ADMIN->value,
        ]);

        $retrievedAdmin = $this->userRepository->getAdminByOrganizationID($organization->id);

        $this->assertEquals($admin->id, $retrievedAdmin->id);
        $this->assertEquals(UserRole::ADMIN, $retrievedAdmin->role);
        $this->assertEquals($organization->id, $retrievedAdmin->organization_id);
    }

    /**
     * Test assigning a role to a user.
     */
    public function test_assign_role_to_user(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $role = Role::create([
            'name' => 'Test Role',
            'guard_name' => 'web',
        ]);

        $this->userRepository->assignRoleToUser($user, [
            'role_id' => $role->id,
        ]);

        $user->refresh();
        $this->assertTrue($user->hasRole($role->name));
    }

    /**
     * Test assigning granular permissions to a user.
     */
    public function test_assign_granular_permissions_to_user(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $permissions = Permission::create([
            'name' => 'test.permission.one',
            'guard_name' => 'web',
        ]);

        $result = $this->userRepository->assignGranularPermissionsToUser(
            $user,
            $permissions->pluck('id')->toArray()
        );

        $this->assertCount(1, $result->getDirectPermissions());
    }

    /**
     * Test removing a role from a user.
     */
    public function test_remove_role_from_user(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $role = Role::create([
            'name' => 'Test Role 2',
            'guard_name' => 'web',
        ]);

        $user->assignRole($role);
        $this->assertTrue($user->fresh()->hasRole($role->name));

        $result = $this->userRepository->removeRoleFromUser($user, $role);

        $this->assertFalse($result->hasRole($role->name));
    }

    /**
     * Test revoking granular permissions from a user.
     */
    public function test_revoke_granular_permissions_from_user(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $permission1 = Permission::create([
            'name' => 'test.permission.one',
            'guard_name' => 'web',
        ]);

        $permission2 = Permission::create([
            'name' => 'test.permission.two',
            'guard_name' => 'web',
        ]);

        $user->givePermissionTo([$permission1, $permission2]);
        $this->assertCount(2, $user->fresh()->getDirectPermissions());

        $result = $this->userRepository->revokeGranularPermissionsFromUser(
            $user,
            [$permission1->id]
        );

        $this->assertCount(1, $result->getDirectPermissions());
    }

    /**
     * Test revoking all permissions from a user.
     */
    public function test_revoke_all_granular_permissions_from_user(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $permission1 = Permission::create([
            'name' => 'test.permission.one',
            'guard_name' => 'web',
        ]);

        $permission2 = Permission::create([
            'name' => 'test.permission.two',
            'guard_name' => 'web',
        ]);

        $user->givePermissionTo([$permission1, $permission2]);
        $this->assertCount(2, $user->fresh()->getDirectPermissions());

        $result = $this->userRepository->revokeGranularPermissionsFromUser(
            $user,
            [$permission1->id, $permission2->id]
        );

        $this->assertCount(0, $result->getDirectPermissions());
    }
}
