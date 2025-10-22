<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Framework;

class FrameworkControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_retrieve_published_frameworks(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::OWNER,
        ]);

        Framework::factory()->count(10)->create(['is_published' => true]);

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


    public function test_user_can_retrieve_published_frameworks_with_type_filter(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        Framework::factory()->create(['name' => 'EU AI Act', 'is_published' => true, 'authority_publisher' => 'Publisher A']);
        Framework::factory()->create(['name' => 'ISO 42001', 'is_published' => true, 'authority_publisher' => 'Publisher B']);

        $response = $this->actingAs($user)->getJson('/api/frameworks?authority_publisher=Publisher A');
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

        $framework = Framework::factory()->create(['is_published' => true]);

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
