<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminOrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Organization',
            'website' => 'https://www.example.com',
            'phone' => '+1-555-0123',
            'country' => 'United States',
            'is_active' => true,
        ], $overrides);
    }

    // Index tests
    public function test_admin_can_view_all_organizations(): void
    {
        Organization::factory(20)->create();

        $response = $this->actingAs($this->adminUser)->getJson('/api/admin/organizations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'website',
                        'phone',
                        'country',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'total',
                'per_page',
            ],
        ]);
        $this->assertCount(10, $response->json('data.data')); // Default per_page is 10
        $this->assertEquals(20, $response->json('data.total'));
    }

    public function test_admin_can_filter_organizations_by_name(): void
    {
        Organization::factory()->create(['name' => 'Acme Corporation']);
        Organization::factory()->create(['name' => 'Tech Solutions Inc']);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/organizations?name=Acme');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('Acme Corporation', $response->json('data.data.0.name'));
    }

    public function test_admin_can_filter_organizations_by_country(): void
    {
        Organization::factory()->create(['country' => 'United States']);
        Organization::factory()->create(['country' => 'Canada']);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/organizations?country=Canada');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('Canada', $response->json('data.data.0.country'));
    }

    public function test_admin_can_filter_organizations_by_status(): void
    {
        Organization::factory()->create(['is_active' => true]);
        Organization::factory()->create(['is_active' => true]);
        Organization::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/organizations?is_active=0');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertFalse($response->json('data.data.0.is_active'));
    }

    public function test_admin_can_paginate_organizations(): void
    {
        Organization::factory(25)->create();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/organizations?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(25, $response->json('data.total'));
    }

    public function test_non_admin_cannot_view_all_organizations(): void
    {
        $user = User::factory()->create(['role' => UserRole::OWNER]);

        $response = $this->actingAs($user)->getJson('/api/admin/organizations');

        $response->assertStatus(403);
    }

    // Store tests
    public function test_admin_can_create_organization(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/organizations', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'name',
                'website',
                'phone',
                'country',
                'is_active',
                'created_at',
                'updated_at',
            ],
        ]);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Organization created successfully', $response->json('message'));
        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'website' => 'https://www.example.com',
        ]);
    }

    public function test_admin_can_create_organization_with_minimal_data(): void
    {
        $payload = [
            'name' => 'Minimal Organization',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/organizations', $payload);

        $response->assertStatus(201);
        $this->assertEquals('Minimal Organization', $response->json('data.name'));
        $this->assertTrue($response->json('data.is_active'));
    }

    public function test_admin_create_validates_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/organizations', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'is_active']);
    }

    public function test_admin_create_validates_unique_website(): void
    {
        Organization::factory()->create(['website' => 'https://www.example.com']);

        $payload = $this->validPayload(['website' => 'https://www.example.com']);

        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/organizations', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['website']);
    }

    public function test_admin_create_validates_unique_phone(): void
    {
        Organization::factory()->create(['phone' => '+1-555-0123']);

        $payload = $this->validPayload(['phone' => '+1-555-0123']);

        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/organizations', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_admin_create_validates_website_format(): void
    {
        $payload = $this->validPayload(['website' => 'invalid-website']);

        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/organizations', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['website']);
    }

    public function test_non_admin_cannot_create_organization(): void
    {
        $user = User::factory()->create(['role' => UserRole::OWNER]);
        $payload = $this->validPayload();

        $response = $this->actingAs($user)->postJson('/api/admin/organizations', $payload);

        $response->assertStatus(403);
    }

    // Show tests
    public function test_admin_can_view_specific_organization(): void
    {
        $organization = Organization::factory()->create();

        $response = $this->actingAs($this->adminUser)->getJson("/api/admin/organizations/{$organization->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'name',
                'website',
                'phone',
                'country',
                'is_active',
                'created_at',
                'updated_at',
            ],
        ]);
        $this->assertEquals($organization->id, $response->json('data.id'));
    }

    public function test_admin_show_returns_404_for_nonexistent(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson('/api/admin/organizations/9999');

        $response->assertStatus(404);
    }

    public function test_non_admin_cannot_view_organization(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => UserRole::OWNER]);

        $response = $this->actingAs($user)->getJson("/api/admin/organizations/{$organization->id}");

        $response->assertStatus(403);
    }

    // Update tests
    public function test_admin_can_update_organization(): void
    {
        $organization = Organization::factory()->create();

        $payload = [
            'name' => 'Updated Organization',
            'country' => 'Canada',
            'is_active' => false,
        ];

        $response = $this->actingAs($this->adminUser)->postJson(
            "/api/admin/organizations/{$organization->id}",
            $payload
        );

        $response->assertStatus(200);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Organization updated successfully', $response->json('message'));
        $this->assertEquals('Updated Organization', $response->json('data.name'));
        $this->assertEquals('Canada', $response->json('data.country'));
        $this->assertFalse($response->json('data.is_active'));
    }

    public function test_admin_update_validates_unique_website(): void
    {
        $org1 = Organization::factory()->create(['website' => 'https://www.org1.com']);
        $org2 = Organization::factory()->create(['website' => 'https://www.org2.com']);

        $payload = ['website' => 'https://www.org1.com'];

        $response = $this->actingAs($this->adminUser)->postJson(
            "/api/admin/organizations/{$org2->id}",
            $payload
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['website']);
    }

    public function test_admin_update_allows_same_phone(): void
    {
        $organization = Organization::factory()->create(['phone' => '+1-555-0123']);

        $payload = ['phone' => '+1-555-0123', 'is_active' => true];

        $response = $this->actingAs($this->adminUser)->postJson(
            "/api/admin/organizations/{$organization->id}",
            $payload
        );

        $response->assertStatus(200);
    }

    public function test_admin_update_returns_404_for_nonexistent(): void
    {
        $payload = ['name' => 'Updated Name'];

        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/organizations/9999', $payload);

        $response->assertStatus(404);
    }

    public function test_non_admin_cannot_update_organization(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => UserRole::OWNER]);
        $payload = ['name' => 'Hacked Name'];

        $response = $this->actingAs($user)->postJson(
            "/api/admin/organizations/{$organization->id}",
            $payload
        );

        $response->assertStatus(403);
    }

    // Delete tests
    public function test_admin_can_delete_organization(): void
    {
        $organization = Organization::factory()->create();

        $response = $this->actingAs($this->adminUser)->deleteJson("/api/admin/organizations/{$organization->id}");

        $response->assertStatus(200);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Organization deleted successfully', $response->json('message'));
        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
    }

    public function test_admin_delete_returns_404_for_nonexistent(): void
    {
        $response = $this->actingAs($this->adminUser)->deleteJson('/api/admin/organizations/9999');

        $response->assertStatus(404);
    }

    public function test_non_admin_cannot_delete_organization(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => UserRole::OWNER]);

        $response = $this->actingAs($user)->deleteJson("/api/admin/organizations/{$organization->id}");

        $response->assertStatus(403);
    }

    public function test_deleting_organization_removes_it_from_database(): void
    {
        $organization = Organization::factory()->create();
        $id = $organization->id;

        $this->actingAs($this->adminUser)->deleteJson("/api/admin/organizations/{$organization->id}");

        $this->assertNull(Organization::find($id));
    }

    // Additional store method tests
    public function test_admin_store_returns_correct_response_structure(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/organizations', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Organization created successfully',
        ]);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Organization created successfully', $response->json('message'));
        $this->assertNotNull($response->json('data.id'));
    }

    public function test_admin_store_persists_all_provided_fields(): void
    {
        $payload = $this->validPayload([
            'website' => 'https://custom-website.io',
            'phone' => '+44-123-4567',
            'country' => 'United Kingdom',
        ]);

        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/organizations', $payload);

        $response->assertStatus(201);
        $organization = Organization::find($response->json('data.id'));

        $this->assertEquals('Test Organization', $organization->name);
        $this->assertEquals('https://custom-website.io', $organization->website);
        $this->assertEquals('+44-123-4567', $organization->phone);
        $this->assertEquals('United Kingdom', $organization->country);
        $this->assertTrue($organization->is_active);
    }
}
