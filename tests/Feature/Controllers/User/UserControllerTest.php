<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_index_returns_organization_users(): void
    {
        $user2 = User::factory()->create(['organization_id' => $this->organization->id]);
        $user3 = User::factory()->create(['organization_id' => $this->organization->id]);

        // Create users from different organization
        User::factory()->create();

        $response = $this->actingAs($this->user)->getJson('/api/organization-users');

        $response->assertOk()
            ->assertJsonStructure(['data', 'message', 'error'])
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Organization users retrieved successfully.');

        // Should only return users from the authenticated user's organization
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_index_returns_paginated_results(): void
    {
        User::factory()->count(25)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/organization-users?per_page=10');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonCount(10, 'data.data');
    }

    public function test_index_with_default_pagination(): void
    {
        User::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/organization-users');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 15)
            ->assertJsonCount(15, 'data.data');
    }

    public function test_index_validates_per_page_parameter(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/organization-users?per_page=999');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_validates_per_page_minimum(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/organization-users?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_returns_correct_user_fields(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/organization-users');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'email', 'role', 'created_at', 'updated_at'],
                    ],
                    'total',
                    'per_page',
                    'current_page',
                ],
            ]);
    }

    public function test_index_does_not_return_password_field(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/organization-users');

        $response->assertOk();

        $userData = $response->json('data.data.0');
        $this->assertArrayNotHasKey('password', $userData);
    }

    public function test_index_returns_empty_list_when_no_users_except_authenticated(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);

        $response = $this->actingAs($user)->getJson('/api/organization-users');

        $response->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/organization-users');

        $response->assertUnauthorized();
    }

    public function test_show_returns_404_for_nonexistent_user(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/organization-users/99999');

        $response->assertNotFound();
    }

    public function test_index_returns_user_role(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/organization-users');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['role'],
                    ],
                ],
            ]);
    }
}
