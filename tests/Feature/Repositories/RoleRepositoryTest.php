<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Permission;
use App\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private RoleRepository $roleRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleRepository = app(RoleRepository::class);
    }

    /**
     * Test getting filtered roles with pagination.
     */
    public function test_get_filtered_roles_returns_paginated_results(): void
    {
        Role::factory()->count(20)->create([
            'guard_name' => 'supabase',
        ]);

        $result = $this->roleRepository->getFilteredRoles([
            'per_page' => 10,
        ]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    /**
     * Test filtering roles by name.
     */
    public function test_get_filtered_roles_filters_by_name(): void
    {
        Role::factory()->create([
            'name' => 'Admin',
            'guard_name' => 'supabase',
        ]);
        Role::factory()->create([
            'name' => 'Contributor',
            'guard_name' => 'supabase',
        ]);
        Role::factory()->create([
            'name' => 'Reviewer',
            'guard_name' => 'supabase',
        ]);

        $result = $this->roleRepository->getFilteredRoles([
            'name' => 'Admin',
            'per_page' => 15,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Admin', $result->items()[0]->name);
    }

    /**
     * Test default pagination per_page is 15.
     */
    public function test_get_filtered_roles_default_pagination(): void
    {
        Role::factory()->count(25)->create([
            'guard_name' => 'supabase',
        ]);

        $result = $this->roleRepository->getFilteredRoles([]);

        $this->assertCount(15, $result->items());
        $this->assertEquals(25, $result->total());
    }

    /**
     * Test creating a role without permissions.
     */
    public function test_create_role_without_permissions(): void
    {
        $roleData = [
            'name' => 'Editor',
            'permissions' => [],
        ];

        $result = $this->roleRepository->createRole($roleData);

        $this->assertEquals('Editor', $result->name);
        $this->assertCount(0, $result->permissions);

        $this->assertDatabaseHas('roles', [
            'name' => 'Editor',
            'guard_name' => 'supabase',
        ]);
    }

    /**
     * Test creating a role with permissions.
     */
    public function test_create_role_with_permissions(): void
    {
        $permissions = Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);

        $roleData = [
            'name' => 'Manager',
            'permissions' => $permissions->pluck('id')->toArray(),
        ];

        $result = $this->roleRepository->createRole($roleData);

        $this->assertEquals('Manager', $result->name);
        $this->assertCount(3, $result->permissions);
    }

    /**
     * Test getting a role by ID.
     */
    public function test_get_role_by_id(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $permissions = Permission::factory()->count(2)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($permissions);

        $retrievedRole = $this->roleRepository->getRoleById($role->id);

        $this->assertNotNull($retrievedRole);
        $this->assertEquals($role->id, $retrievedRole->id);
        $this->assertEquals($role->name, $retrievedRole->name);
        $this->assertCount(2, $retrievedRole->permissions);
    }

    /**
     * Test getting a non-existent role returns null.
     */
    public function test_get_role_by_id_returns_null_for_non_existent_role(): void
    {
        $retrievedRole = $this->roleRepository->getRoleById(9999);

        $this->assertNull($retrievedRole);
    }

    /**
     * Test assigning permissions to a role.
     */
    public function test_assign_permissions_to_role(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $permissions = Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);

        $this->roleRepository->assignPermissionsToRole($role, $permissions->pluck('id')->toArray());

        $this->assertCount(3, $role->fresh()->permissions);
    }

    /**
     * Test assigning multiple permissions to a role with existing permissions.
     */
    public function test_assign_additional_permissions_to_role(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $existingPermissions = Permission::factory()->count(2)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($existingPermissions);

        $newPermissions = Permission::factory()->count(2)->create([
            'guard_name' => 'supabase',
        ]);

        $this->roleRepository->assignPermissionsToRole($role, $newPermissions->pluck('id')->toArray());

        // Should have all permissions (existing + new)
        $this->assertCount(4, $role->fresh()->permissions);
    }

    /**
     * Test revoking permissions from a role.
     */
    public function test_revoke_permissions_from_role(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $permissions = Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($permissions);

        $this->assertCount(3, $role->fresh()->permissions);

        $this->roleRepository->revokePermissionsFromRole($role, $permissions->take(2)->pluck('id')->toArray());

        $this->assertCount(1, $role->fresh()->permissions);
    }

    /**
     * Test revoking all permissions from a role.
     */
    public function test_revoke_all_permissions_from_role(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $permissions = Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($permissions);

        $this->roleRepository->revokePermissionsFromRole($role, $permissions->pluck('id')->toArray());

        $this->assertCount(0, $role->fresh()->permissions);
    }

    /**
     * Test eager loading of permissions in filtered roles.
     */
    public function test_get_filtered_roles_eager_loads_permissions(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $permissions = Permission::factory()->count(5)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($permissions);

        $result = $this->roleRepository->getFilteredRoles([]);
        $retrievedRole = $result->items()[0];

        $this->assertTrue($retrievedRole->relationLoaded('permissions'));
        $this->assertCount(5, $retrievedRole->permissions);
    }

    /**
     * Test eager loading of permissions in get role by ID.
     */
    public function test_get_role_by_id_eager_loads_permissions(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $permissions = Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($permissions);

        $retrievedRole = $this->roleRepository->getRoleById($role->id);

        $this->assertTrue($retrievedRole->relationLoaded('permissions'));
    }

    public function test_update_role_name(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);

        $data = [
            'name' => 'Updated Role Name',
        ];

        $this->roleRepository->updateRole($role, $data);

        $this->assertEquals('Updated Role Name', $role->fresh()->name);
    }

    public function test_update_role_permissions(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $initialPermissions = Permission::factory()->count(2)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($initialPermissions);

        $newPermissions = Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);

        $data = [
            'permissions' => $newPermissions->pluck('id')->toArray(),
        ];

        $this->roleRepository->updateRole($role, $data);

        $this->assertCount(3, $role->fresh()->permissions);
        foreach ($newPermissions as $permission) {
            $this->assertTrue($role->fresh()->hasPermissionTo($permission));
        }
    }
}
