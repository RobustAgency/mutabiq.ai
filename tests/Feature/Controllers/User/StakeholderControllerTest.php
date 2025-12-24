<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Enums\Stakeholder\Type;
use App\Enums\Stakeholder\Status;
use App\Enums\Stakeholder\Classification;
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
                            'display_id',
                            'organization_id',
                            'type',
                            'display_name',
                            'first_name',
                            'last_name',
                            'email',
                            'phone',
                            'org_unit',
                            'role_tags',
                            'timezone',
                            'classification',
                            'country',
                            'external_ref',
                            'status',
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
            'type' => Type::PERSON->value,
            'display_name' => 'John Smith',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'org_unit' => 'Engineering',
            'role_tags' => ['admin', 'developer'],
            'timezone' => 'America/New_York',
            'classification' => Classification::INTERNAL->value,
            'country' => 'US',
            'status' => Status::ACTIVE->value,
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

    public function test_statistics_returns_correct_counts(): void
    {
        Stakeholder::factory()->count(6)->create([
            'organization_id' => $this->organization->id,
            'classification' => Classification::INTERNAL->value,
        ]);
        Stakeholder::factory()->count(4)->create([
            'organization_id' => $this->organization->id,
            'classification' => Classification::EXTERNAL->value,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder statistics retrieved successfully.',
                'data' => [
                    'total_count' => 10,
                    'internal_count' => 6,
                    'external_count' => 4,
                ],
            ]);
    }

    public function test_statistics_requires_authentication(): void
    {
        $response = $this->getJson('/api/stakeholders/statistics');

        $response->assertStatus(401);
    }

    public function test_statistics_returns_only_organization_data(): void
    {
        $anotherOrg = Organization::factory()->create();

        Stakeholder::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
            'classification' => Classification::INTERNAL->value,
        ]);
        Stakeholder::factory()->count(3)->create([
            'organization_id' => $anotherOrg->id,
            'classification' => Classification::INTERNAL->value,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total_count' => 5,
                    'internal_count' => 5,
                    'external_count' => 0,
                ],
            ]);
    }

    public function test_statistics_with_empty_organization(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder statistics retrieved successfully.',
                'data' => [
                    'total_count' => 0,
                    'internal_count' => 0,
                    'external_count' => 0,
                ],
            ]);
    }
}
