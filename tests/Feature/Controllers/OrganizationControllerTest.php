<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_super_admin_can_view_list_of_organizations(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        Organization::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/admin/organizations');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Organizations retrieved successfully',
        ]);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'country', 'website', 'is_active'],
                ],
                'current_page',
                'per_page',
                'total',
            ],
        ]);
    }

    public function test_user_can_create_organization(): void
    {
        $user = User::factory()->create(['role' => UserRole::OWNER]);

        $organizationData = [
            'name' => $this->faker->company(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ];

        $response = $this->actingAs($user)->postJson('/api/organizations', $organizationData);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Organization created successfully',
            'data' => null,
        ]);

        $this->assertDatabaseHas('organizations', [
            'name' => $organizationData['name'],
            'website' => $organizationData['website'],
            'phone' => $organizationData['phone'],
            'country' => $organizationData['country'],
            'user_id' => $user->id,
        ]);
    }

    public function test_super_admin_can_view_single_organization(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $user = User::factory()->create(['organization_id' => null]);
        $organization = Organization::factory()->create(['user_id' => $user->id]);
        $user->update(['organization_id' => $organization->id]);

        $response = $this->actingAs($admin)->getJson("/api/admin/organizations/{$organization->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Organization retrieved successfully',
            'data' => [
                'id' => $organization->id,
            ],
        ]);
    }

    public function test_super_admin_can_inactivate_organization(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $user = User::factory()->create(['organization_id' => null]);
        $organization = Organization::factory()->create(['user_id' => $user->id]);
        $user->update(['organization_id' => $organization->id]);

        $updateData = [
            'is_active' => false,
        ];

        $response = $this->actingAs($admin)->postJson("/api/admin/organizations/{$organization->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Organization updated successfully',
            'data' => null,
        ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'is_active' => false,
        ]);
    }

    public function test_super_admin_can_update_organization(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $user = User::factory()->create(['organization_id' => null]);
        $organization = Organization::factory()->create(['user_id' => $user->id]);
        $user->update(['organization_id' => $organization->id]);

        $updateData = [
            'name' => 'Updated Organization Name',
            'website' => 'https://updated-website.com',
            'phone' => '123-456-7890',
            'country' => 'Updated Country',
            
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->postJson("/api/admin/organizations/{$organization->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Organization updated successfully',
            'data' => null,
        ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Organization Name',
            'website' => 'https://updated-website.com',
            'phone' => '123-456-7890',
            'country' => 'Updated Country',
            'is_active' => true,
        ]);
    }
}
