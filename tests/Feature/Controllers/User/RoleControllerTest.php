<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test getting all roles returns paginated results.
     */
    public function test_index_returns_paginated_roles(): void
    {
        Role::factory()->count(20)->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'data',
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Roles retrieved successfully',
            ]);

        $this->assertCount(15, $response->json('data.data')); // Default per_page is 15
        $this->assertEquals(20, $response->json('data.total'));
    }

    /**
     * Test getting roles with custom per_page parameter.
     */
    public function test_index_returns_roles_with_custom_pagination(): void
    {
        Role::factory()->count(25)->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->getJson('/api/roles?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data.data'));
        $this->assertEquals(25, $response->json('data.total'));
        $this->assertEquals(10, $response->json('data.per_page'));
    }

    /**
     * Test filtering roles by name.
     */
    public function test_index_filters_roles_by_name(): void
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

        $response = $this->actingAs($this->user, 'supabase')->getJson('/api/roles?name=Admin');

        $response->assertStatus(200);
        $roles = $response->json('data.data');
        $this->assertCount(1, $roles);
        $this->assertEquals('Admin', $roles[0]['name']);
    }

    /**
     * Test getting roles returns empty when no roles exist.
     */
    public function test_index_returns_empty_when_no_roles(): void
    {
        $response = $this->actingAs($this->user, 'supabase')->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Roles retrieved successfully',
            ]);

        $this->assertCount(0, $response->json('data.data'));
    }

    /**
     * Test show returns a specific role with permissions.
     */
    public function test_show_returns_specific_role_with_permissions(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $permissions = Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($permissions);

        $response = $this->actingAs($this->user, 'supabase')->getJson("/api/roles/{$role->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'name',
                    'guard_name',
                    'permissions',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Role retrieved successfully',
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => 'supabase',
                ],
            ]);

        $this->assertCount(3, $response->json('data.permissions'));
    }

    /**
     * Test show returns 404 for non-existent role.
     */
    public function test_show_returns_404_for_non_existent_role(): void
    {
        $response = $this->actingAs($this->user, 'supabase')->getJson('/api/roles/9999');

        $response->assertStatus(404);
    }

    /**
     * Test show includes role permissions in response.
     */
    public function test_show_includes_permissions_in_response(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $permissions = Permission::factory()->count(2)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($permissions);

        $response = $this->actingAs($this->user, 'supabase')->getJson("/api/roles/{$role->id}");

        $response->assertStatus(200);
        $permissionsData = $response->json('data.permissions');

        $this->assertCount(2, $permissionsData);
        foreach ($permissionsData as $permission) {
            $this->assertArrayHasKey('id', $permission);
            $this->assertArrayHasKey('name', $permission);
        }
    }

    /**
     * Test show returns role with no permissions.
     */
    public function test_show_returns_role_with_no_permissions(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->getJson("/api/roles/{$role->id}");

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data.permissions'));
    }

    /**
     * Test index response structure is valid.
     */
    public function test_index_response_has_valid_structure(): void
    {
        Role::factory()->count(5)->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'guard_name',
                        ],
                    ],
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ]);
    }

    /**
     * Test index with pagination links.
     */
    public function test_index_includes_pagination_metadata(): void
    {
        Role::factory()->count(30)->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->getJson('/api/roles?per_page=10');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('per_page', $data);
        $this->assertArrayHasKey('current_page', $data);
        $this->assertArrayHasKey('last_page', $data);
        $this->assertEquals(30, $data['total']);
        $this->assertEquals(10, $data['per_page']);
        $this->assertEquals(3, $data['last_page']);
    }

    /**
     * Test pagination maintains filter across pages.
     */
    public function test_index_pagination_maintains_filters(): void
    {
        Role::factory()->create([
            'name' => 'Admin Role',
            'guard_name' => 'supabase',
        ]);
        Role::factory()->count(20)->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->getJson('/api/roles?name=Admin&per_page=10');

        $response->assertStatus(200);
        $roles = $response->json('data.data');

        $this->assertCount(1, $roles);
        $this->assertEquals('Admin Role', $roles[0]['name']);
    }

    /**
     * Test store creates a new role successfully.
     */
    public function test_store_creates_role_successfully(): void
    {
        $response = $this->actingAs($this->user, 'supabase')->postJson('/api/roles', [
            'name' => 'Editor',
            'permissions' => [],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'name',
                    'guard_name',
                    'permissions',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Role created successfully',
                'data' => [
                    'name' => 'Editor',
                    'guard_name' => 'supabase',
                ],
            ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Editor',
            'guard_name' => 'supabase',
        ]);
    }

    /**
     * Test store creates role with permissions.
     */
    public function test_store_creates_role_with_permissions(): void
    {
        $permissions = Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->postJson('/api/roles', [
            'name' => 'Moderator',
            'permissions' => $permissions->pluck('id')->toArray(),
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Role created successfully',
            ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Moderator',
        ]);

        $role = Role::where('name', 'Moderator')->first();
        $this->assertCount(3, $role->permissions);
    }

    /**
     * Test store validates required name field.
     */
    public function test_store_validates_required_name(): void
    {
        $response = $this->actingAs($this->user, 'supabase')->postJson('/api/roles', [
            'permissions' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test store validates unique role name.
     */
    public function test_store_validates_unique_name(): void
    {
        Role::factory()->create([
            'name' => 'Duplicate',
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->postJson('/api/roles', [
            'name' => 'Duplicate',
            'permissions' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test store validates permissions are valid IDs.
     */
    public function test_store_validates_permission_ids(): void
    {
        $response = $this->actingAs($this->user, 'supabase')->postJson('/api/roles', [
            'name' => 'TestRole',
            'permissions' => [9999],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('permissions.0');
    }

    /**
     * Test update updates role name successfully.
     */
    public function test_update_updates_role_name(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->postJson("/api/roles/{$role->id}", [
            'name' => 'Updated Role Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'name',
                    'guard_name',
                    'permissions',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Role updated successfully',
                'data' => [
                    'id' => $role->id,
                    'name' => 'Updated Role Name',
                ],
            ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Updated Role Name',
        ]);
    }

    /**
     * Test update syncs permissions on role.
     */
    public function test_update_syncs_permissions(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $oldPermissions = Permission::factory()->count(2)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($oldPermissions);

        $newPermissions = Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->postJson("/api/roles/{$role->id}", [
            'name' => $role->name,
            'permissions' => $newPermissions->pluck('id')->toArray(),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Role updated successfully',
            ]);

        $this->assertCount(3, $response->json('data.permissions'));
        $role->refresh();
        $this->assertCount(3, $role->permissions);
    }

    /**
     * Test update removes permissions when empty array provided.
     */
    public function test_update_removes_permissions_when_empty_array(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $permissions = Permission::factory()->count(2)->create([
            'guard_name' => 'supabase',
        ]);
        $role->givePermissionTo($permissions);

        $response = $this->actingAs($this->user, 'supabase')->postJson("/api/roles/{$role->id}", [
            'name' => $role->name,
            'permissions' => [],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
            ]);

        $this->assertCount(0, $response->json('data.permissions'));
    }

    /**
     * Test update validates unique name on same role.
     */
    public function test_update_allows_same_name(): void
    {
        $role = Role::factory()->create([
            'name' => 'OriginalName',
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->postJson("/api/roles/{$role->id}", [
            'name' => 'OriginalName',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
            ]);
    }

    /**
     * Test update validates unique name against other roles.
     */
    public function test_update_validates_unique_name_against_other_roles(): void
    {
        $role1 = Role::factory()->create([
            'name' => 'Role1',
            'guard_name' => 'supabase',
        ]);
        Role::factory()->create([
            'name' => 'Role2',
            'guard_name' => 'supabase',
        ]);

        $response = $this->actingAs($this->user, 'supabase')->postJson("/api/roles/{$role1->id}", [
            'name' => 'Role2',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test update with non-existent role returns 404.
     */
    public function test_update_returns_404_for_non_existent_role(): void
    {
        $response = $this->actingAs($this->user, 'supabase')->postJson('/api/roles/9999', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test update with partial data (only name).
     */
    public function test_update_with_partial_data(): void
    {
        $role = Role::factory()->create([
            'guard_name' => 'supabase',
        ]);
        $originalName = $role->name;

        $response = $this->actingAs($this->user, 'supabase')->postJson("/api/roles/{$role->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'data' => [
                    'name' => 'New Name',
                ],
            ]);
    }
}
