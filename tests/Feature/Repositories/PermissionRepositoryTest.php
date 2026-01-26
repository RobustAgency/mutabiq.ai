<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Permission;
use App\Repositories\PermissionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PermissionRepository $permissionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionRepository = app(PermissionRepository::class);
    }

    /**
     * Test getting all permissions groups them by module and screen.
     */
    public function test_get_all_permissions_returns_grouped_permissions(): void
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
            'name' => 'risk-management.incidents.view',
            'guard_name' => 'supabase',
        ]);

        $result = $this->permissionRepository->getAllPermissions();

        // Should be grouped by module first, then screen
        $this->assertArrayHasKey('core-assets', $result);
        $this->assertArrayHasKey('risk-management', $result);
        $this->assertArrayHasKey('ai-models', $result['core-assets']);
        $this->assertArrayHasKey('datasets', $result['core-assets']);
        $this->assertArrayHasKey('incidents', $result['risk-management']);
    }

    /**
     * Test getting all permissions returns correct permission count per group.
     */
    public function test_get_all_permissions_returns_correct_permission_count(): void
    {
        Permission::factory()->count(5)->create([
            'guard_name' => 'supabase',
        ]);

        $result = $this->permissionRepository->getAllPermissions();

        // Flatten and count all permissions
        $totalPermissions = 0;
        foreach ($result as $module) {
            foreach ($module as $screen) {
                $totalPermissions += count($screen);
            }
        }

        $this->assertEquals(5, $totalPermissions);
    }

    /**
     * Test getting all permissions handles empty database.
     */
    public function test_get_all_permissions_returns_empty_collection_when_no_permissions(): void
    {
        $result = $this->permissionRepository->getAllPermissions();

        $this->assertEmpty($result);
    }

    /**
     * Test getting all permissions with different guard names.
     */
    public function test_get_all_permissions_includes_all_guard_names(): void
    {
        Permission::factory()->create([
            'name' => 'core-assets.ai-models.view',
            'guard_name' => 'supabase',
        ]);
        Permission::factory()->create([
            'name' => 'core-assets.ai-models.view',
            'guard_name' => 'api',
        ]);
        Permission::factory()->create([
            'name' => 'core-assets.ai-models.view',
            'guard_name' => 'web',
        ]);

        $result = $this->permissionRepository->getAllPermissions();

        // Should have grouped all permissions regardless of guard
        $aiModelsPermissions = $result['core-assets']['ai-models'];
        $this->assertCount(3, $aiModelsPermissions);
    }

    /**
     * Test getting all permissions correctly parses permission names.
     */
    public function test_get_all_permissions_parses_permission_names_correctly(): void
    {
        Permission::factory()->create([
            'name' => 'privacy.consent.view',
            'guard_name' => 'supabase',
        ]);
        Permission::factory()->create([
            'name' => 'privacy.consent.edit',
            'guard_name' => 'supabase',
        ]);

        $result = $this->permissionRepository->getAllPermissions();

        $consentPermissions = $result['privacy']['consent'];
        $this->assertCount(2, $consentPermissions);

        // Verify both permissions are present
        $permissionNames = $consentPermissions->pluck('name')->toArray();
        $this->assertContains('privacy.consent.view', $permissionNames);
        $this->assertContains('privacy.consent.edit', $permissionNames);
    }
}
