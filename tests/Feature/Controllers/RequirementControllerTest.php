<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Framework;
use App\Models\Requirement;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequirementControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_super_admin_can_list_their_requirements(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        Requirement::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/admin/requirements');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirements retrieved successfully',
        ]);
    }

    public function test_super_admin_can_store_requirement(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework = Framework::factory()->create(['user_id' => $user->id]);

        $payload = [
            'reference' => 'REQ-001',
            'requirement_text' => 'The system shall ensure data encryption at rest.',
            'category' => 'security',
            'applicability' => 'All AI systems handling sensitive data.',
            'effective_from' => '2024-01-01',
            'effective_to' => '2025-01-01',
            'supersedes_req_id' => null,
            'superseded_by_req_id' => null,
            'priority' => 'high',
            'tags' => ['security', 'compliance'],
            'framework_ids' => [$framework->id],
        ];

        $response = $this->actingAs($user)->postJson('/api/admin/requirements', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement created successfully',
        ]);

        $this->assertDatabaseHas('requirements', [
            'reference' => 'REQ-001',
            'category' => 'security',
            'priority' => 'high',
        ]);
    }

    public function test_super_admin_can_view_single_requirement(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework = Framework::factory()->create(['user_id' => $user->id]);
        $requirement = Requirement::factory()->create();

        $requirement->frameworks()->attach($framework->id);

        $response = $this->actingAs($user)->getJson("/api/admin/requirements/{$requirement->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement retrieved successfully',
            'data' => [
                'id' => $requirement->id,
            ],
        ]);
    }

    public function test_super_admin_can_update_requirement(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework1 = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Framework 1']);
        $framework2 = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Framework 2']);
        $requirement = Requirement::factory()->create(['reference' => 'Old Requirement Name']);
        $requirement->frameworks()->attach($framework2->id);

        $payload = [
            'reference' => 'REQ-002',
            'requirement_text' => 'The system shall ensure data encryption in transit.',
            'category' => 'security',
            'applicability' => 'All AI systems handling sensitive data.',
            'effective_from' => '2024-01-01',
            'effective_to' => '2025-01-01',
            'supersedes_req_id' => null,
            'superseded_by_req_id' => null,
            'priority' => 'high',
            'tags' => ['security', 'compliance'],
            'framework_ids' => [$framework1->id, $framework2->id],
        ];

        $response = $this->actingAs($user)->postJson("/api/admin/requirements/{$requirement->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement updated successfully',
        ]);

        $this->assertDatabaseHas('requirements', [
            'id' => $requirement->id,
            'reference' => 'REQ-002',
        ]);
    }

    public function test_super_admin_can_unlink_framework_from_requirement(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        $framework1 = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Framework 1']);
        $framework2 = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Framework 2']);

        $requirement = Requirement::factory()->create(['reference' => 'Linked Requirement']);

        $requirement->frameworks()->attach([$framework1->id, $framework2->id]);

        $payload = [
            'reference' => 'Linked Requirement',
            'framework_ids' => [$framework1->id],
        ];

        $response = $this->actingAs($user)->postJson("/api/admin/requirements/{$requirement->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement updated successfully',
        ]);

        $requirement->refresh();
        $this->assertTrue($requirement->frameworks->contains($framework1->id));
        $this->assertFalse($requirement->frameworks->contains($framework2->id));
    }
}
