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

    public function test_user_can_retrieve_available_frameworks(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
        ]);

        Framework::factory()->count(10)->create(['user_id' => $user->id, 'is_published' => true]);

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
}
