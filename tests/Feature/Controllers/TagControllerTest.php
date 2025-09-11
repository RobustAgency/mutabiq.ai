<?php

namespace Tests\Feature\Controllers;

use App\Models\Tag;
use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_super_admin_can_view_list_of_tags(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        Tag::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/admin/tags');
        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Tags retrieved successfully',
        ]);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'group'],
                ],
                'current_page',
                'per_page',
                'total',
            ],
        ]);
    }

    public function test_super_admin_can_create_tags(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        $tagData = [
            'group' => $this->faker->word(),
            'names' => [$this->faker->word(), $this->faker->word()],
        ];

        $response = $this->actingAs($user)->postJson('/api/admin/tags', $tagData);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Tags created successfully',
            'data' => null,
        ]);
    }
}
