<?php

namespace Tests\Feature\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Framework;

class FrameworkControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_it_can_retrieve_available_frameworks(): void
    {
        $user = User::factory()->create();

        Framework::factory()->count(10)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/frameworks');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Frameworks retrieved successfully',
        ]);
    }
}
