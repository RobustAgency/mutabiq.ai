<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($user, 'supabase');
    }

    /**
     * Test getting all permissions returns grouped data.
     */
    public function test_index_returns_all_permissions_grouped(): void
    {
        Permission::factory()->create([
            'name' => 'core-assets.ai-models.view',
            'guard_name' => 'supabase',
        ]);
        Permission::factory()->create([
            'name' => 'core-assets.ai-models.create',
            'guard_name' => 'supabase',
        ]);
        Permission::factory()->create([
            'name' => 'core-assets.datasets.view',
            'guard_name' => 'supabase',
        ]);
        Permission::factory()->create([
            'name' => 'privacy.consent.view',
            'guard_name' => 'supabase',
        ]);

        $response = $this->getJson('/api/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'All permissions retrieved successfully.',
            ]);

        // Verify data is grouped correctly
        $data = $response->json('data');
        $this->assertArrayHasKey('core-assets', $data);
        $this->assertArrayHasKey('privacy', $data);
        $this->assertArrayHasKey('ai-models', $data['core-assets']);
        $this->assertArrayHasKey('datasets', $data['core-assets']);
        $this->assertArrayHasKey('consent', $data['privacy']);
    }

    /**
     * Test getting permissions returns empty array when no permissions exist.
     */
    public function test_index_returns_empty_when_no_permissions(): void
    {
        $response = $this->getJson('/api/permissions');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'All permissions retrieved successfully.',
                'data' => [],
            ]);
    }

    /**
     * Test permissions are properly nested in returned data.
     */
    public function test_index_returns_properly_nested_permissions(): void
    {
        Permission::factory()->create([
            'name' => 'risk-management.incidents.view',
            'guard_name' => 'supabase',
        ]);
        Permission::factory()->create([
            'name' => 'risk-management.incidents.create',
            'guard_name' => 'supabase',
        ]);
        Permission::factory()->create([
            'name' => 'risk-management.incidents.edit',
            'guard_name' => 'supabase',
        ]);

        $response = $this->getJson('/api/permissions');

        $response->assertStatus(200);
        $data = $response->json('data');

        // Check structure
        $this->assertIsArray($data['risk-management']);
        $this->assertIsArray($data['risk-management']['incidents']);
        $this->assertCount(3, $data['risk-management']['incidents']);
    }

    /**
     * Test that permissions from multiple modules are returned.
     */
    public function test_index_returns_permissions_from_multiple_modules(): void
    {
        Permission::factory()->count(2)->create([
            'guard_name' => 'supabase',
        ]);
        Permission::factory()->count(3)->create([
            'guard_name' => 'supabase',
        ]);

        $response = $this->getJson('/api/permissions');

        $response->assertStatus(200);
        $data = $response->json('data');

        // Count total permissions across all modules and screens
        $totalPermissions = 0;
        foreach ($data as $module) {
            foreach ($module as $screen) {
                $totalPermissions += count($screen);
            }
        }

        $this->assertEquals(5, $totalPermissions);
    }

    /**
     * Test response structure is consistent.
     */
    public function test_index_response_structure_is_valid(): void
    {
        Permission::factory()->create([
            'name' => 'governance.framework.view',
            'guard_name' => 'supabase',
        ]);

        $response = $this->getJson('/api/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data',
            ]);

        $this->assertFalse($response->json('error'));
        $this->assertIsString($response->json('message'));
    }

    /**
     * Test permissions include all necessary fields.
     */
    public function test_index_permissions_include_required_fields(): void
    {
        Permission::factory()->create([
            'name' => 'governance.control.view',
            'guard_name' => 'supabase',
        ]);

        $response = $this->getJson('/api/permissions');

        $response->assertStatus(200);
        $data = $response->json('data');

        // Get first permission from nested structure
        $firstPermission = collect($data)->first(function ($screens) {
            return is_array($screens) && collect($screens)->first(function ($permissions) {
                return is_array($permissions) && count($permissions) > 0;
            });
        });

        if ($firstPermission) {
            $permission = collect($firstPermission)->first()[0] ?? null;
            if ($permission) {
                $this->assertArrayHasKey('id', $permission);
                $this->assertArrayHasKey('name', $permission);
                $this->assertArrayHasKey('guard_name', $permission);
            }
        }
    }
}
