<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Stakeholder;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StakeholderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_index_returns_paginated_stakeholders(): void
    {
        Stakeholder::factory()->count(5)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholders retrieved successfully.',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'organization_id',
                            'type',
                            'display_name',
                            'legal_name',
                            'email',
                            'phone',
                            'org_unit',
                            'vendor_id',
                            'role_tags',
                            'timezone',
                            'classification',
                            'country',
                            'external_ref',
                            'active',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_index_validates_per_page_minimum(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_validates_per_page_maximum(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?per_page=101');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/stakeholders');

        $response->assertStatus(401);
    }

    public function test_store_creates_stakeholder(): void
    {
        $data = [
            'type' => 'vendor_org',
            'display_name' => 'John Smith',
            'legal_name' => 'Tech Company',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'org_unit' => 'Engineering',
            'role_tags' => ['admin', 'developer'],
            'timezone' => 'America/New_York',
            'classification' => 'internal',
            'country' => 'US',
            'active' => true,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/stakeholders', $data);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder created successfully.',
            ])
            ->assertJsonPath('data.display_name', 'John Smith')
            ->assertJsonPath('data.email', 'john@example.com');

        $this->assertDatabaseHas('stakeholders', [
            'display_name' => 'John Smith',
            'email' => 'john@example.com',
        ]);
    }

    public function test_show_returns_stakeholder(): void
    {
        $stakeholder = Stakeholder::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/stakeholders/{$stakeholder->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder retrieved successfully.',
            ])
            ->assertJsonPath('data.id', $stakeholder->id)
            ->assertJsonPath('data.display_name', $stakeholder->display_name);
    }

    public function test_show_returns_404_for_non_existent_stakeholder(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders/99999');

        $response->assertStatus(404);
    }

    public function test_update_updates_stakeholder(): void
    {
        $stakeholder = Stakeholder::factory()->create([
            'display_name' => 'Original Name',
        ]);

        $updateData = [
            'type' => $stakeholder->type,
            'display_name' => 'Updated Name',
            'email' => $stakeholder->email,
            'timezone' => $stakeholder->timezone,
            'classification' => $stakeholder->classification,
            'active' => $stakeholder->active,
            'role_tags' => $stakeholder->role_tags,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/stakeholders/{$stakeholder->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder updated successfully.',
            ]);

        $this->assertDatabaseHas('stakeholders', [
            'id' => $stakeholder->id,
            'display_name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_stakeholder(): void
    {
        $stakeholder = Stakeholder::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/stakeholders/{$stakeholder->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder deleted successfully.',
            ]);

        $this->assertDatabaseMissing('stakeholders', [
            'id' => $stakeholder->id,
        ]);
    }

    public function test_destroy_returns_404_for_non_existent_stakeholder(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/stakeholders/99999');

        $response->assertStatus(404);
    }
}
