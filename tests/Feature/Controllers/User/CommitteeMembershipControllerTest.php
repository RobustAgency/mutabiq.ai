<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiCommittee;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\CommitteeMembership;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\CommitteeMembership\MemberRole;
use App\Enums\CommitteeMembership\Eligibility;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeMembershipControllerTest extends TestCase
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
     * Test index returns all memberships with default pagination
     */
    public function test_index_returns_all_memberships(): void
    {
        CommitteeMembership::factory(5)->create();

        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships');

        $response->assertOk()
            ->assertJsonStructure(['data', 'message', 'error'])
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Committee memberships retrieved successfully.');
    }

    /**
     * Test index returns paginated results with default limit of 15
     */
    public function test_index_returns_paginated_results(): void
    {
        CommitteeMembership::factory(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 15)
            ->assertJsonCount(15, 'data.data');
    }

    /**
     * Test index with custom per_page parameter
     */
    public function test_index_with_custom_per_page(): void
    {
        CommitteeMembership::factory(25)->create();

        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships?per_page=10');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonCount(10, 'data.data');
    }

    /**
     * Test index validates per_page parameter - maximum value
     */
    public function test_index_validates_per_page_maximum(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships?per_page=999');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Test index validates per_page parameter - minimum value
     */
    public function test_index_validates_per_page_minimum(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Test index filter by ai_committee_id
     */
    public function test_index_filter_by_ai_committee_id(): void
    {
        $committee = AiCommittee::factory()->create();
        CommitteeMembership::factory(5)->create(['ai_committee_id' => $committee->id]);
        CommitteeMembership::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-memberships?ai_committee_id='.$committee->id);

        $response->assertOk()
            ->assertJsonPath('data.total', 5);

        $this->assertTrue(
            collect($response->json('data.data'))
                ->every(fn ($item) => $item['ai_committee_id'] === $committee->id)
        );
    }

    /**
     * Test index filter by stakeholder_id
     */
    public function test_index_filter_by_stakeholder_id(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        CommitteeMembership::factory(4)->create(['stakeholder_id' => $stakeholder->id]);
        CommitteeMembership::factory(6)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-memberships?stakeholder_id='.$stakeholder->id);

        $response->assertOk()
            ->assertJsonPath('data.total', 4);
    }

    /**
     * Test index filter by member_role
     */
    public function test_index_filter_by_member_role(): void
    {
        CommitteeMembership::factory(5)->create(['member_role' => MemberRole::CHAIR->value]);
        CommitteeMembership::factory(3)->create(['member_role' => MemberRole::ADVISOR->value]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-memberships?member_role='.MemberRole::CHAIR->value);

        $response->assertOk()
            ->assertJsonPath('data.total', 5);

        $this->assertTrue(
            collect($response->json('data.data'))
                ->every(fn ($item) => $item['member_role'] === MemberRole::CHAIR->value)
        );
    }

    /**
     * Test index filter by eligibility
     */
    public function test_index_filter_by_eligibility(): void
    {
        CommitteeMembership::factory(4)->create(['eligibility' => Eligibility::ACTIVE->value]);
        CommitteeMembership::factory(6)->create(['eligibility' => Eligibility::SUSPENDED->value]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-memberships?eligibility='.Eligibility::ACTIVE->value);

        $response->assertOk()
            ->assertJsonPath('data.total', 4);

        $this->assertTrue(
            collect($response->json('data.data'))
                ->every(fn ($item) => $item['eligibility'] === Eligibility::ACTIVE->value)
        );
    }

    /**
     * Test index filter by active status (no end_date)
     */
    public function test_index_filter_by_active(): void
    {
        CommitteeMembership::factory(8)->create(['end_date' => null]);
        CommitteeMembership::factory(5)->create(['end_date' => now()->subMonth()]);

        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships?active=1');

        $response->assertOk()
            ->assertJsonPath('data.total', 8);

        $this->assertTrue(
            collect($response->json('data.data'))
                ->every(fn ($item) => $item['end_date'] === null)
        );
    }

    /**
     * Test index with multiple filters combined
     */
    public function test_index_with_multiple_filters(): void
    {
        $committee = AiCommittee::factory()->create();
        CommitteeMembership::factory(10)->create([
            'ai_committee_id' => $committee->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
        ]);

        CommitteeMembership::factory(5)->create([
            'ai_committee_id' => $committee->id,
            'member_role' => MemberRole::ADVISOR->value,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-memberships?ai_committee_id='.$committee->id.'&member_role='.MemberRole::CHAIR->value.'&eligibility='.Eligibility::ACTIVE->value);

        $response->assertOk()
            ->assertJsonPath('data.total', 10);
    }

    /**
     * Test index returns correct resource structure
     */
    public function test_index_returns_correct_resource_structure(): void
    {
        CommitteeMembership::factory(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'ai_committee_id', 'stakeholder_id', 'member_role', 'eligibility', 'start_date', 'end_date', 'expertise_tags', 'created_at', 'updated_at'],
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
     * Test index returns empty list when no memberships exist
     */
    public function test_index_returns_empty_list(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships');

        $response->assertOk()
            ->assertJsonPath('data.total', 0)
            ->assertJsonCount(0, 'data.data');
    }

    /**
     * Test unauthenticated user cannot access index
     */
    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/committee-memberships');

        $response->assertUnauthorized();
    }

    /**
     * Test store creates a new membership
     */
    public function test_store_creates_membership(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'expertise_tags' => ['governance', 'ethics'],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

        $response->assertCreated()
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Committee membership created successfully.')
            ->assertJsonPath('data.ai_committee_id', $committee->id)
            ->assertJsonPath('data.stakeholder_id', $stakeholder->id)
            ->assertJsonPath('data.member_role', MemberRole::CHAIR->value)
            ->assertJsonPath('data.eligibility', Eligibility::ACTIVE->value);

        $this->assertDatabaseHas('committee_memberships', [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
        ]);
    }

    /**
     * Test store returns 201 created status
     */
    public function test_store_returns_201_created(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::ADVISOR->value,
            'eligibility' => Eligibility::SUSPENDED->value,
            'start_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * Test store validates required fields
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_committee_id', 'stakeholder_id', 'member_role', 'eligibility', 'start_date']);
    }

    /**
     * Test store validates ai_committee_id exists
     */
    public function test_store_validates_ai_committee_id_exists(): void
    {
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => 99999,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'start_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_committee_id']);
    }

    /**
     * Test store validates stakeholder_id exists
     */
    public function test_store_validates_stakeholder_id_exists(): void
    {
        $committee = AiCommittee::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => 99999,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'start_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stakeholder_id']);
    }

    /**
     * Test store validates member_role enum
     */
    public function test_store_validates_member_role_enum(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => 'invalid_role',
            'eligibility' => Eligibility::ACTIVE->value,
            'start_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['member_role']);
    }

    /**
     * Test store validates eligibility enum
     */
    public function test_store_validates_eligibility_enum(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => 'invalid_eligibility',
            'start_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['eligibility']);
    }

    /**
     * Test store validates start_date is a date
     */
    public function test_store_validates_start_date_is_date(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'start_date' => 'not_a_date',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    /**
     * Test store validates end_date is after start_date
     */
    public function test_store_validates_end_date_after_start_date(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'start_date' => now()->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /**
     * Test store with all member_role enum values
     */
    public function test_store_with_all_member_role_values(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        foreach (MemberRole::cases() as $role) {
            $data = [
                'ai_committee_id' => $committee->id,
                'stakeholder_id' => $stakeholder->id,
                'member_role' => $role->value,
                'eligibility' => Eligibility::ACTIVE->value,
                'start_date' => now()->toDateString(),
            ];

            $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

            $response->assertCreated()
                ->assertJsonPath('data.member_role', $role->value);
        }
    }

    /**
     * Test store returns membership with timestamps
     */
    public function test_store_returns_membership_with_timestamps(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'start_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

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
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'start_date' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/committee-memberships', $data);

        $response->assertUnauthorized();
    }

    /**
     * Test show returns specific membership
     */
    public function test_show_returns_membership(): void
    {
        $membership = CommitteeMembership::factory()->create([
            'member_role' => MemberRole::ADVISOR->value,
            'eligibility' => Eligibility::ACTIVE->value,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships/'.$membership->id);

        $response->assertOk()
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Committee membership retrieved successfully.')
            ->assertJsonPath('data.id', $membership->id)
            ->assertJsonPath('data.member_role', MemberRole::ADVISOR->value)
            ->assertJsonPath('data.eligibility', Eligibility::ACTIVE->value);
    }

    /**
     * Test show returns correct resource structure
     */
    public function test_show_returns_correct_structure(): void
    {
        $membership = CommitteeMembership::factory()->create();

        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships/'.$membership->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'ai_committee_id', 'stakeholder_id', 'member_role', 'eligibility', 'start_date', 'end_date', 'expertise_tags', 'created_at', 'updated_at'],
                'message',
                'error',
            ]);
    }

    /**
     * Test show returns 404 for non-existent membership
     */
    public function test_show_returns_404_for_nonexistent(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/committee-memberships/99999');

        $response->assertNotFound();
    }

    /**
     * Test unauthenticated user cannot access show
     */
    public function test_unauthenticated_user_cannot_show(): void
    {
        $membership = CommitteeMembership::factory()->create();

        $response = $this->getJson('/api/committee-memberships/'.$membership->id);

        $response->assertUnauthorized();
    }

    /**
     * Test update modifies membership
     */
    public function test_update_modifies_membership(): void
    {
        $membership = CommitteeMembership::factory()->create([
            'member_role' => MemberRole::ADVISOR->value,
            'eligibility' => Eligibility::ACTIVE->value,
        ]);

        $updateData = [
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::SUSPENDED->value,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/committee-memberships/'.$membership->id, $updateData);

        $response->assertOk()
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Committee membership updated successfully.')
            ->assertJsonPath('data.member_role', MemberRole::CHAIR->value)
            ->assertJsonPath('data.eligibility', Eligibility::SUSPENDED->value);

        $this->assertDatabaseHas('committee_memberships', [
            'id' => $membership->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::SUSPENDED->value,
        ]);
    }

    /**
     * Test update with partial data
     */
    public function test_update_with_partial_data(): void
    {
        $membership = CommitteeMembership::factory()->create([
            'member_role' => MemberRole::ADVISOR->value,
            'eligibility' => Eligibility::ACTIVE->value,
        ]);

        $updateData = [
            'member_role' => MemberRole::CHAIR->value,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/committee-memberships/'.$membership->id, $updateData);

        $response->assertOk()
            ->assertJsonPath('data.member_role', MemberRole::CHAIR->value)
            ->assertJsonPath('data.eligibility', Eligibility::ACTIVE->value);
    }

    /**
     * Test update validates member_role enum
     */
    public function test_update_validates_member_role_enum(): void
    {
        $membership = CommitteeMembership::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/committee-memberships/'.$membership->id, [
                'member_role' => 'invalid_role',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['member_role']);
    }

    /**
     * Test update validates eligibility enum
     */
    public function test_update_validates_eligibility_enum(): void
    {
        $membership = CommitteeMembership::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/committee-memberships/'.$membership->id, [
                'eligibility' => 'invalid_eligibility',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['eligibility']);
    }

    /**
     * Test update with empty data succeeds
     */
    public function test_update_with_empty_data_succeeds(): void
    {
        $membership = CommitteeMembership::factory()->create([
            'member_role' => MemberRole::ADVISOR->value,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/committee-memberships/'.$membership->id, []);

        $response->assertOk()
            ->assertJsonPath('data.member_role', MemberRole::ADVISOR->value);
    }

    /**
     * Test update with all member_role enum values
     */
    public function test_update_with_all_member_role_values(): void
    {
        $membership = CommitteeMembership::factory()->create();

        foreach (MemberRole::cases() as $role) {
            $response = $this->actingAs($this->user)
                ->postJson('/api/committee-memberships/'.$membership->id, [
                    'member_role' => $role->value,
                ]);

            $response->assertOk()
                ->assertJsonPath('data.member_role', $role->value);
        }
    }

    /**
     * Test update returns 404 for non-existent membership
     */
    public function test_update_returns_404_for_nonexistent(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/committee-memberships/99999', [
                'member_role' => MemberRole::CHAIR->value,
            ]);

        $response->assertNotFound();
    }

    /**
     * Test unauthenticated user cannot update
     */
    public function test_unauthenticated_user_cannot_update(): void
    {
        $membership = CommitteeMembership::factory()->create();

        $response = $this->postJson('/api/committee-memberships/'.$membership->id, [
            'member_role' => MemberRole::CHAIR->value,
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test destroy deletes membership
     */
    public function test_destroy_deletes_membership(): void
    {
        $membership = CommitteeMembership::factory()->create();
        $id = $membership->id;

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/committee-memberships/'.$id);

        $response->assertOk()
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Committee membership deleted successfully.')
            ->assertJsonPath('data', null);

        $this->assertNull(CommitteeMembership::find($id));
    }

    /**
     * Test destroy returns 404 for non-existent membership
     */
    public function test_destroy_returns_404_for_nonexistent(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/committee-memberships/99999');

        $response->assertNotFound();
    }

    /**
     * Test unauthenticated user cannot destroy
     */
    public function test_unauthenticated_user_cannot_destroy(): void
    {
        $membership = CommitteeMembership::factory()->create();

        $response = $this->deleteJson('/api/committee-memberships/'.$membership->id);

        $response->assertUnauthorized();
    }

    /**
     * Test destroy removes membership from database
     */
    public function test_destroy_removes_from_database(): void
    {
        $membership = CommitteeMembership::factory()->create();

        $this->assertDatabaseHas('committee_memberships', ['id' => $membership->id]);

        $this->actingAs($this->user)->deleteJson('/api/committee-memberships/'.$membership->id);

        $this->assertDatabaseMissing('committee_memberships', ['id' => $membership->id]);
    }

    /**
     * Test multiple memberships can be stored
     */
    public function test_multiple_memberships_can_be_stored(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $committee = AiCommittee::factory()->create();
            $stakeholder = Stakeholder::factory()->create();

            $data = [
                'ai_committee_id' => $committee->id,
                'stakeholder_id' => $stakeholder->id,
                'member_role' => MemberRole::cases()[$i % count(MemberRole::cases())]->value,
                'eligibility' => Eligibility::cases()[$i % count(Eligibility::cases())]->value,
                'start_date' => now()->toDateString(),
            ];

            $response = $this->actingAs($this->user)->postJson('/api/committee-memberships', $data);

            $response->assertCreated();
        }

        $this->assertDatabaseCount('committee_memberships', 5);
    }

    /**
     * Test update preserves created_at timestamp
     */
    public function test_update_preserves_created_at(): void
    {
        $membership = CommitteeMembership::factory()->create();
        $originalCreatedAt = $membership->created_at;

        sleep(1);

        $this->actingAs($this->user)
            ->postJson('/api/committee-memberships/'.$membership->id, [
                'member_role' => MemberRole::CHAIR->value,
            ]);

        $updated = CommitteeMembership::find($membership->id);
        $this->assertEquals($originalCreatedAt->getTimestamp(), $updated->created_at->getTimestamp());
    }
}
