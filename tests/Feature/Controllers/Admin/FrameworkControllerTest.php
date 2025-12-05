<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Framework;
use App\Enums\Framework\Status;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FrameworkControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_super_admin_can_list_their_frameworks(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        Framework::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/admin/frameworks');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Frameworks retrieved successfully',
        ]);
    }

    public function test_super_admin_can_store_framework(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        $payload = [
            'name' => 'EU AI Act',
            'version' => '1.0',
            'jurisdictions' => ['EU'],
            'scope' => 'Comprehensive regulation for governing AI systems in the EU.',
            'status' => Status::ACTIVE->value,
            'effective_date' => now()->toDateTimeString(),
            'source_url' => 'https://example.com',

        ];

        $response = $this->actingAs($user)->postJson('/api/admin/frameworks', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Framework created successfully',
        ]);

        $this->assertDatabaseHas('frameworks', [
            'name' => 'EU AI Act',
            'user_id' => $user->id,
        ]);
    }

    public function test_super_admin_can_view_single_framework(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework = Framework::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/admin/frameworks/{$framework->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Framework retrieved successfully',
            'data' => ['id' => $framework->id],
        ]);
    }

    public function test_super_admin_can_update_framework(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $payload = [
            'name' => 'Updated Framework Name',
            'version' => '2.0',
            'jurisdictions' => ['US', 'EU'],
            'scope' => 'Updated scope description.',
            'status' => Status::RETIRED->value,
            'effective_date' => now()->toDateTimeString(),
            'source_url' => 'https://updated-source-url.com',
        ];

        $response = $this->actingAs($user)->postJson("/api/admin/frameworks/{$framework->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Framework updated successfully',
            'data' => null,
        ]);

        $this->assertDatabaseHas('frameworks', [
            'id' => $framework->id,
            'name' => 'Updated Framework Name',
        ]);
    }
}
