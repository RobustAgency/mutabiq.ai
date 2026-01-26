<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use App\Models\Organization;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        setPermissionsTeamId($this->organization->id);
    }

    public function test_index_returns_organization_users(): void
    {
        $user2 = User::factory()->create(['organization_id' => $this->organization->id]);
        $user3 = User::factory()->create(['organization_id' => $this->organization->id]);

        // Create users from different organization
        User::factory()->create();

        $response = $this->actingAs($this->user)->getJson('/api/users');

        $response->assertOk()
            ->assertJsonStructure(['data', 'message', 'error'])
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Organization users retrieved successfully.');

        // Should only return users from the authenticated user's organization
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_index_returns_paginated_results(): void
    {
        User::factory()->count(25)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/users?per_page=10');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonCount(10, 'data.data');
    }

    public function test_index_with_default_pagination(): void
    {
        User::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/users');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 15)
            ->assertJsonCount(15, 'data.data');
    }

    public function test_index_validates_per_page_parameter(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/users?per_page=999');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_validates_per_page_minimum(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/users?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_returns_correct_user_fields(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'email', 'role', 'created_at', 'updated_at'],
                    ],
                    'total',
                    'per_page',
                    'current_page',
                ],
            ]);
    }

    public function test_index_does_not_return_password_field(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/users');

        $response->assertOk();

        $userData = $response->json('data.data.0');
        $this->assertArrayNotHasKey('password', $userData);
    }

    public function test_index_returns_empty_list_when_no_users_except_authenticated(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);

        $response = $this->actingAs($user)->getJson('/api/users');

        $response->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    public function test_show_returns_404_for_nonexistent_user(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/users/99999');

        $response->assertNotFound();
    }

    public function test_index_returns_user_role(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['role'],
                    ],
                ],
            ]);
    }

    /**
     * Test assigning role to user.
     */
    public function test_assign_role_to_user(): void
    {
        $targetUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'supabase',
        ]);

        $this->user->assignRole($adminRole);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/users/{$targetUser->id}/assign-role", [
                'role_id' => $adminRole->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Role assigned to user successfully.',
                'error' => false,
            ]);

        $this->assertTrue($targetUser->fresh()->hasRole($adminRole->name));
    }

    /**
     * Test revoking role from user.
     */
    public function test_revoke_role_from_user(): void
    {
        $targetUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $roleUser = Role::create([
            'name' => 'user',
            'guard_name' => 'supabase',
        ]);
        $targetUser->assignRole($roleUser);

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'supabase',
        ]);
        $this->user->assignRole($adminRole);

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/users/{$targetUser->id}/revoke-role/{$roleUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Role revoked from user successfully.',
                'error' => false,
            ]);

        $this->assertFalse($targetUser->fresh()->hasRole($roleUser->name));
    }

    /**
     * Test assigning permissions to user.
     */
    public function test_assign_permissions_to_user(): void
    {
        $targetUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $permission1 = Permission::create([
            'name' => 'test.permission.one',
            'guard_name' => 'supabase',
        ]);

        $permission2 = Permission::create([
            'name' => 'test.permission.two',
            'guard_name' => 'supabase',
        ]);

        $permission3 = Permission::create([
            'name' => 'test.permission.three',
            'guard_name' => 'supabase',
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'supabase',
        ]);
        $this->user->assignRole($adminRole);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/users/{$targetUser->id}/assign-permission", [
                'permissions' => [$permission1->id, $permission2->id, $permission3->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Permission assigned to user successfully.',
                'error' => false,
            ]);

        $targetUser->refresh();
        $this->assertCount(3, $targetUser->getDirectPermissions());
    }

    /**
     * Test revoking permissions from user.
     */
    public function test_revoke_permissions_from_user(): void
    {
        $targetUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $permission1 = Permission::create([
            'name' => 'test.permission.one',
            'guard_name' => 'supabase',
        ]);
        $permission2 = Permission::create([
            'name' => 'test.permission.two',
            'guard_name' => 'supabase',
        ]);
        $permission3 = Permission::create([
            'name' => 'test.permission.three',
            'guard_name' => 'supabase',
        ]);
        $targetUser->givePermissionTo([$permission1, $permission2, $permission3]);

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'supabase',
        ]);
        $this->user->assignRole($adminRole);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/users/{$targetUser->id}/revoke-permission", [
                'permissions' => [$permission1->id, $permission2->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Permission revoked from user successfully.',
                'error' => false,
            ]);

        $targetUser->refresh();
        $this->assertCount(1, $targetUser->getDirectPermissions());
    }

    /**
     * Test assign role validates role exists.
     */
    public function test_assign_role_validates_role_exists(): void
    {
        $targetUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'supabase',
        ]);
        $this->user->assignRole($adminRole);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/users/{$targetUser->id}/assign-role", [
                'role_id' => 9999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_id']);
    }

    /**
     * Test assign permissions validates permissions exist.
     */
    public function test_assign_permissions_validates_permissions_exist(): void
    {
        $targetUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'supabase',
        ]);
        $this->user->assignRole($adminRole);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/users/{$targetUser->id}/assign-permission", [
                'permission_ids' => [9999],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test assign permissions returns user resource.
     */
    public function test_assign_permissions_returns_user_resource(): void
    {
        $targetUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $permission = Permission::create([
            'name' => 'test.permission.one',
            'guard_name' => 'supabase',
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'supabase',
        ]);
        $this->user->assignRole($adminRole);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/users/{$targetUser->id}/assign-permission", [
                'permissions' => [$permission->id],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
                'message',
                'error',
            ]);
    }

    /**
     * Test revoke permissions validates at least one permission.
     */
    public function test_revoke_permissions_requires_at_least_one(): void
    {
        $targetUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'supabase',
        ]);
        $this->user->assignRole($adminRole);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/users/{$targetUser->id}/revoke-permission", [
                'permission_ids' => [],
            ]);

        $response->assertStatus(422);
    }
}
