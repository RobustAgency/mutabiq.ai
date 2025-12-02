<?php

namespace Tests\Feature\Controllers\User;

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

    public function test_user_can_retrieve_published_frameworks(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::OWNER,
        ]);

        Framework::factory()->count(10)->create(['effective_date' => now()->subDays(1)]);

        // Explicitly set per_page to 10 to match rules and avoid ambiguity
        $response = $this->actingAs($user)->getJson('/api/frameworks');
        $data = $response->json('data.data') ?? $response->json('data');
        $this->assertCount(10, $data);
        $response->assertOk();
        $response->assertJson([
            'error' => false,
            'message' => 'Frameworks retrieved successfully',
        ]);
    }

    public function test_user_can_retrieve_published_frameworks_with_status_filter(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        Framework::factory()->create(['name' => 'EU AI Act', 'effective_date' => now()->subDays(1), 'status' => Status::ACTIVE->value]);
        Framework::factory()->create(['name' => 'ISO 42001', 'effective_date' => now()->subDays(1), 'status' => Status::RETIRED->value]);

        $response = $this->actingAs($user)->getJson('/api/frameworks?status='.Status::ACTIVE->value);
        $data = $response->json('data.data') ?? $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('EU AI Act', $data[0]['name']);
        $response->assertOk();
        $response->assertJson([
            'error' => false,
            'message' => 'Frameworks retrieved successfully',
        ]);
    }

    public function test_user_can_retrieve_framework_by_id(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::OWNER,
        ]);

        $framework = Framework::factory()->create(['effective_date' => now()->subDays(1)]);

        $response = $this->actingAs($user)->getJson("/api/frameworks/{$framework->id}");
        $data = $response->json('data');
        $this->assertEquals($framework->id, $data['id']);
        $response->assertOk();
        $response->assertJson([
            'error' => false,
            'message' => 'Framework retrieved successfully',
        ]);
    }
}
