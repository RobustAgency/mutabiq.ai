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

        Requirement::factory()->count(3)->create(['user_id' => $user->id]);

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
            'name' => 'EU AI Act',
            'code' => 'MRF-1',
            'description' => 'Comprehensive regulation for governing AI systems in the EU.',
            'framework_ids' => [$framework->id],
        ];

        $response = $this->actingAs($user)->postJson('/api/admin/requirements', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement created successfully',
        ]);

        $this->assertDatabaseHas('requirements', [
            'name' => 'EU AI Act',
            'user_id' => $user->id,
        ]);
    }

    public function test_super_admin_can_view_single_requirement(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework = Framework::factory()->create(['user_id' => $user->id]);
        $requirement = Requirement::factory()->create(['user_id' => $user->id]);

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
        $requirement = Requirement::factory()->create(['name' => 'Old Requirement Name', 'user_id' => $user->id]);
        $requirement->frameworks()->attach($framework2->id);

        $payload = [
            'name' => 'Updated Requirement Name',
            'code' => 'MRF-2',
            'description' => 'Updated description for the requirement.',
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
            'name' => 'Updated Requirement Name',
        ]);
    }

    public function test_super_admin_can_unlink_framework_from_requirement(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        $framework1 = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Framework 1']);
        $framework2 = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Framework 2']);

        $requirement = Requirement::factory()->create(['name' => 'Linked Requirement', 'user_id' => $user->id]);

        $requirement->frameworks()->attach([$framework1->id, $framework2->id]);

        $payload = [
            'name' => 'Linked Requirement',
            'code' => 'MRF-3',
            'description' => 'Requirement after unlinking one framework.',
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
