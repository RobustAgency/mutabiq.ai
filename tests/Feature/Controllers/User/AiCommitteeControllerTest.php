<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiCommittee;
use App\Models\Organization;
use App\Enums\AiCommittee\Type;
use App\Enums\AiCommittee\Cadence;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiCommitteeControllerTest extends TestCase
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

    /**
     * Test index returns all committees with default pagination
     */
    public function test_index_returns_all_committees(): void
    {
        AiCommittee::factory(5)->create();

        $response = $this->actingAs($this->user)->getJson('/api/ai-committees');

        $response->assertOk()
            ->assertJsonStructure(['data', 'message', 'error'])
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'AI Committees retrieved successfully.');
    }

    /**
     * Test index returns paginated results with default limit of 15
     */
    public function test_index_returns_paginated_results(): void
    {
        AiCommittee::factory(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/ai-committees');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 15)
            ->assertJsonCount(15, 'data.data');
    }

    /**
     * Test index with custom per_page parameter
     */
    public function test_index_with_custom_per_page(): void
    {
        AiCommittee::factory(25)->create();

        $response = $this->actingAs($this->user)->getJson('/api/ai-committees?per_page=10');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonCount(10, 'data.data');
    }

    /**
     * Test index validates per_page parameter - maximum value
     */
    public function test_index_validates_per_page_maximum(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/ai-committees?per_page=999');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Test index validates per_page parameter - minimum value
     */
    public function test_index_validates_per_page_minimum(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/ai-committees?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Test index filter by type
     */
    public function test_index_filter_by_type(): void
    {
        AiCommittee::factory(5)->create(['type' => Type::GOVERNANCE->value]);
        AiCommittee::factory(3)->create(['type' => Type::ETHICS->value]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ai-committees?type='.Type::GOVERNANCE->value);

        $response->assertOk()
            ->assertJsonPath('data.total', 5);

        $this->assertTrue(
            collect($response->json('data.data'))
                ->every(fn ($item) => $item['type'] === Type::GOVERNANCE->value)
        );
    }

    /**
     * Test index filter by cadence
     */
    public function test_index_filter_by_cadence(): void
    {
        AiCommittee::factory(4)->create(['cadence' => Cadence::MONTHLY->value]);
        AiCommittee::factory(6)->create(['cadence' => Cadence::QUARTERLY->value]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ai-committees?cadence='.Cadence::MONTHLY->value);

        $response->assertOk()
            ->assertJsonPath('data.total', 4);

        $this->assertTrue(
            collect($response->json('data.data'))
                ->every(fn ($item) => $item['cadence'] === Cadence::MONTHLY->value)
        );
    }

    /**
     * Test index filter by active status
     */
    public function test_index_filter_by_active_true(): void
    {
        AiCommittee::factory(8)->active()->create();
        AiCommittee::factory(5)->inactive()->create();

        $response = $this->actingAs($this->user)->getJson('/api/ai-committees?active=1');

        $response->assertOk()
            ->assertJsonPath('data.total', 8);

        $this->assertTrue(
            collect($response->json('data.data'))
                ->every(fn ($item) => $item['active'] === true)
        );
    }

    /**
     * Test index filter by inactive status
     */
    public function test_index_filter_by_active_false(): void
    {
        AiCommittee::factory(7)->active()->create();
        AiCommittee::factory(6)->inactive()->create();

        $response = $this->actingAs($this->user)->getJson('/api/ai-committees?active=0');

        $response->assertOk()
            ->assertJsonPath('data.total', 6);

        $this->assertTrue(
            collect($response->json('data.data'))
                ->every(fn ($item) => $item['active'] === false)
        );
    }

    /**
     * Test index filter by name with partial match
     */
    public function test_index_filter_by_name(): void
    {
        AiCommittee::factory()->create(['name' => 'Governance Committee']);
        AiCommittee::factory()->create(['name' => 'Ethics Board']);
        AiCommittee::factory()->create(['name' => 'Risk Management Committee']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ai-committees?name=Committee');

        $response->assertOk()
            ->assertJsonPath('data.total', 2);
    }

    /**
     * Test index with multiple filters combined
     */
    public function test_index_with_multiple_filters(): void
    {
        AiCommittee::factory(10)->create([
            'type' => Type::GOVERNANCE->value,
            'cadence' => Cadence::MONTHLY->value,
            'active' => true,
        ]);

        AiCommittee::factory(5)->create([
            'type' => Type::GOVERNANCE->value,
            'cadence' => Cadence::QUARTERLY->value,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ai-committees?type='.Type::GOVERNANCE->value.'&cadence='.Cadence::MONTHLY->value.'&active=1');

        $response->assertOk()
            ->assertJsonPath('data.total', 10);
    }

    /**
     * Test index returns correct resource structure
     */
    public function test_index_returns_correct_resource_structure(): void
    {
        AiCommittee::factory(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/ai-committees');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'type', 'charter', 'cadence', 'owner_team', 'active', 'created_at', 'updated_at'],
                    ],
                    'total',
                    'per_page',
                    'current_page',
                ],
                'message',
                'error',
            ]);
    }

    /**
     * Test index returns empty list when no committees exist
     */
    public function test_index_returns_empty_list(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/ai-committees');

        $response->assertOk()
            ->assertJsonPath('data.total', 0)
            ->assertJsonCount(0, 'data.data');
    }

    /**
     * Test unauthenticated user cannot access index
     */
    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/ai-committees');

        $response->assertUnauthorized();
    }

    /**
     * Test store creates a new committee
     */
    public function test_store_creates_committee(): void
    {
        $data = [
            'name' => 'AI Governance Committee',
            'type' => Type::GOVERNANCE->value,
            'charter' => 'Committee charter document',
            'cadence' => Cadence::MONTHLY->value,
            'owner_team' => 'Executive Team',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

        $response->assertCreated()
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'AI Committee created successfully.')
            ->assertJsonPath('data.name', 'AI Governance Committee')
            ->assertJsonPath('data.type', Type::GOVERNANCE->value)
            ->assertJsonPath('data.cadence', Cadence::MONTHLY->value)
            ->assertJsonPath('data.active', true);

        $this->assertDatabaseHas('ai_committees', ['name' => 'AI Governance Committee']);
    }

    /**
     * Test store returns 201 created status
     */
    public function test_store_returns_201_created(): void
    {
        $data = [
            'name' => 'Ethics Committee',
            'type' => Type::ETHICS->value,
            'charter' => 'Ethics charter',
            'cadence' => Cadence::QUARTERLY->value,
            'owner_team' => 'Chief Ethics Officer',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * Test store validates required fields
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/ai-committees', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'charter', 'cadence', 'owner_team', 'active']);
    }

    /**
     * Test store validates name field
     */
    public function test_store_validates_name_field(): void
    {
        $data = [
            'name' => 'A', // Too short
            'type' => Type::GOVERNANCE->value,
            'charter' => 'Charter',
            'cadence' => Cadence::MONTHLY->value,
            'owner_team' => 'Team',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test store validates type enum
     */
    public function test_store_validates_type_enum(): void
    {
        $data = [
            'name' => 'Test Committee',
            'type' => 'invalid_type',
            'charter' => 'Charter',
            'cadence' => Cadence::MONTHLY->value,
            'owner_team' => 'Team',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /**
     * Test store validates cadence enum
     */
    public function test_store_validates_cadence_enum(): void
    {
        $data = [
            'name' => 'Test Committee',
            'type' => Type::GOVERNANCE->value,
            'charter' => 'Charter',
            'cadence' => 'invalid_cadence',
            'owner_team' => 'Team',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cadence']);
    }

    /**
     * Test store validates active field is boolean
     */
    public function test_store_validates_active_boolean(): void
    {
        $data = [
            'name' => 'Test Committee',
            'type' => Type::GOVERNANCE->value,
            'charter' => 'Charter',
            'cadence' => Cadence::MONTHLY->value,
            'owner_team' => 'Team',
            'active' => 'not_boolean',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['active']);
    }

    /**
     * Test store with all type enum values
     */
    public function test_store_with_all_type_values(): void
    {
        foreach (Type::cases() as $type) {
            $data = [
                'name' => 'Committee '.$type->value,
                'type' => $type->value,
                'charter' => 'Charter',
                'cadence' => Cadence::MONTHLY->value,
                'owner_team' => 'Team',
                'active' => true,
            ];

            $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

            $response->assertCreated()
                ->assertJsonPath('data.type', $type->value);
        }
    }

    /**
     * Test store with all cadence enum values
     */
    public function test_store_with_all_cadence_values(): void
    {
        foreach (Cadence::cases() as $cadence) {
            $data = [
                'name' => 'Committee '.$cadence->value,
                'type' => Type::GOVERNANCE->value,
                'charter' => 'Charter',
                'cadence' => $cadence->value,
                'owner_team' => 'Team',
                'active' => true,
            ];

            $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

            $response->assertCreated()
                ->assertJsonPath('data.cadence', $cadence->value);
        }
    }

    /**
     * Test store returns new committee with timestamps
     */
    public function test_store_returns_committee_with_timestamps(): void
    {
        $data = [
            'name' => 'Test Committee',
            'type' => Type::GOVERNANCE->value,
            'charter' => 'Charter',
            'cadence' => Cadence::MONTHLY->value,
            'owner_team' => 'Team',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['created_at', 'updated_at']])
            ->assertJsonPath('data.created_at', fn ($value) => ! empty($value))
            ->assertJsonPath('data.updated_at', fn ($value) => ! empty($value));
    }

    /**
     * Test unauthenticated user cannot store
     */
    public function test_unauthenticated_user_cannot_store(): void
    {
        $data = [
            'name' => 'Test Committee',
            'type' => Type::GOVERNANCE->value,
            'charter' => 'Charter',
            'cadence' => Cadence::MONTHLY->value,
            'owner_team' => 'Team',
            'active' => true,
        ];

        $response = $this->postJson('/api/ai-committees', $data);

        $response->assertUnauthorized();
    }

    /**
     * Test show returns specific committee
     */
    public function test_show_returns_committee(): void
    {
        $committee = AiCommittee::factory()->create([
            'name' => 'Test Committee',
            'type' => Type::ETHICS->value,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/ai-committees/'.$committee->id);

        $response->assertOk()
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'AI Committee retrieved successfully.')
            ->assertJsonPath('data.id', $committee->id)
            ->assertJsonPath('data.name', 'Test Committee')
            ->assertJsonPath('data.type', Type::ETHICS->value);
    }

    /**
     * Test show returns correct resource structure
     */
    public function test_show_returns_correct_structure(): void
    {
        $committee = AiCommittee::factory()->create();

        $response = $this->actingAs($this->user)->getJson('/api/ai-committees/'.$committee->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'type', 'charter', 'cadence', 'owner_team', 'active', 'created_at', 'updated_at'],
                'message',
                'error',
            ]);
    }

    /**
     * Test show returns 404 for non-existent committee
     */
    public function test_show_returns_404_for_nonexistent(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/ai-committees/99999');

        $response->assertNotFound();
    }

    /**
     * Test unauthenticated user cannot access show
     */
    public function test_unauthenticated_user_cannot_show(): void
    {
        $committee = AiCommittee::factory()->create();

        $response = $this->getJson('/api/ai-committees/'.$committee->id);

        $response->assertUnauthorized();
    }

    /**
     * Test update modifies committee
     */
    public function test_update_modifies_committee(): void
    {
        $committee = AiCommittee::factory()->create([
            'name' => 'Original Name',
            'type' => Type::GOVERNANCE->value,
            'active' => true,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'type' => Type::ETHICS->value,
            'active' => false,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-committees/'.$committee->id, $updateData);

        $response->assertOk()
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'AI Committee updated successfully.')
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.type', Type::ETHICS->value)
            ->assertJsonPath('data.active', false);

        $this->assertDatabaseHas('ai_committees', [
            'id' => $committee->id,
            'name' => 'Updated Name',
            'type' => Type::ETHICS->value,
            'active' => false,
        ]);
    }

    /**
     * Test update with partial data
     */
    public function test_update_with_partial_data(): void
    {
        $committee = AiCommittee::factory()->create([
            'name' => 'Original Name',
            'type' => Type::GOVERNANCE->value,
            'charter' => 'Original Charter',
        ]);

        $updateData = [
            'name' => 'Updated Name',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-committees/'.$committee->id, $updateData);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.type', Type::GOVERNANCE->value)
            ->assertJsonPath('data.charter', 'Original Charter');
    }

    /**
     * Test update validates type enum
     */
    public function test_update_validates_type_enum(): void
    {
        $committee = AiCommittee::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-committees/'.$committee->id, [
                'type' => 'invalid_type',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /**
     * Test update validates cadence enum
     */
    public function test_update_validates_cadence_enum(): void
    {
        $committee = AiCommittee::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-committees/'.$committee->id, [
                'cadence' => 'invalid_cadence',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cadence']);
    }

    /**
     * Test update with empty data succeeds
     */
    public function test_update_with_empty_data_succeeds(): void
    {
        $committee = AiCommittee::factory()->create([
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-committees/'.$committee->id, []);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Original Name');
    }

    /**
     * Test update with all type enum values
     */
    public function test_update_with_all_type_values(): void
    {
        $committee = AiCommittee::factory()->create();

        foreach (Type::cases() as $type) {
            $response = $this->actingAs($this->user)
                ->postJson('/api/ai-committees/'.$committee->id, [
                    'type' => $type->value,
                ]);

            $response->assertOk()
                ->assertJsonPath('data.type', $type->value);
        }
    }

    /**
     * Test update returns 404 for non-existent committee
     */
    public function test_update_returns_404_for_nonexistent(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-committees/99999', [
                'name' => 'Updated Name',
            ]);

        $response->assertNotFound();
    }

    /**
     * Test unauthenticated user cannot update
     */
    public function test_unauthenticated_user_cannot_update(): void
    {
        $committee = AiCommittee::factory()->create();

        $response = $this->postJson('/api/ai-committees/'.$committee->id, [
            'name' => 'Updated Name',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test destroy deletes committee
     */
    public function test_destroy_deletes_committee(): void
    {
        $committee = AiCommittee::factory()->create();
        $id = $committee->id;

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/ai-committees/'.$id);

        $response->assertOk()
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'AI Committee deleted successfully.')
            ->assertJsonPath('data', null);

        $this->assertNull(AiCommittee::find($id));
    }

    /**
     * Test destroy returns 404 for non-existent committee
     */
    public function test_destroy_returns_404_for_nonexistent(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/ai-committees/99999');

        $response->assertNotFound();
    }

    /**
     * Test unauthenticated user cannot destroy
     */
    public function test_unauthenticated_user_cannot_destroy(): void
    {
        $committee = AiCommittee::factory()->create();

        $response = $this->deleteJson('/api/ai-committees/'.$committee->id);

        $response->assertUnauthorized();
    }

    /**
     * Test destroy removes committee from database
     */
    public function test_destroy_removes_from_database(): void
    {
        $committee = AiCommittee::factory()->create(['name' => 'Test Committee']);

        $this->assertDatabaseHas('ai_committees', ['id' => $committee->id]);

        $this->actingAs($this->user)->deleteJson('/api/ai-committees/'.$committee->id);

        $this->assertDatabaseMissing('ai_committees', ['id' => $committee->id]);
    }

    /**
     * Test update preserves created_at timestamp
     */
    public function test_update_preserves_created_at(): void
    {
        $committee = AiCommittee::factory()->create();
        $originalCreatedAt = $committee->created_at;

        sleep(1); // Ensure time passes

        $this->actingAs($this->user)
            ->postJson('/api/ai-committees/'.$committee->id, [
                'name' => 'Updated Name',
            ]);

        $updated = AiCommittee::find($committee->id);
        $this->assertEquals($originalCreatedAt->getTimestamp(), $updated->created_at->getTimestamp());
    }

    /**
     * Test multiple committees can be stored
     */
    public function test_multiple_committees_can_be_stored(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $data = [
                'name' => "Committee $i",
                'type' => Type::cases()[$i % count(Type::cases())]->value,
                'charter' => "Charter $i",
                'cadence' => Cadence::cases()[$i % count(Cadence::cases())]->value,
                'owner_team' => "Team $i",
                'active' => $i % 2 === 0,
            ];

            $response = $this->actingAs($this->user)->postJson('/api/ai-committees', $data);

            $response->assertCreated();
        }

        $this->assertDatabaseCount('ai_committees', 5);
    }
}
