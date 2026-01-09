<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Models\UseCase;
use App\Models\CommitteeMeeting;
use App\Models\CommitteeDecision;
use App\Enums\CommitteeDecision\VoteMethod;
use App\Enums\CommitteeDecision\VoteResult;
use App\Enums\CommitteeDecision\DecisionType;
use App\Enums\CommitteeDecision\DecisionScope;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeDecisionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected CommitteeMeeting $meeting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->meeting = CommitteeMeeting::factory()->create();
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'committee_meeting_id' => $this->meeting->id,
            'decision_type' => DecisionType::APPROVE->value,
            'decision_scope' => DecisionScope::MODEL->value,
            'vote_method' => VoteMethod::SIMPLE_MAJORITY->value,
            'vote_result' => VoteResult::PASSED->value,
            'rationale' => 'Model meets compliance requirements',
            'owner_team' => 'ai_governance',
        ], $overrides);
    }

    // Index tests
    public function test_index_returns_paginated_committee_decisions(): void
    {
        CommitteeDecision::factory(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/committee-decisions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'committee_meeting_id',
                        'decision_type',
                        'decision_scope',
                        'ai_model_id',
                        'use_case_id',
                        'control_id',
                        'related_ref',
                        'rationale',
                        'conditions',
                        'expiry_date',
                        'vote_method',
                        'vote_result',
                        'owner_team',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);
        $this->assertCount(15, $response->json('data.data'));
        $this->assertEquals(20, $response->json('data.total'));
    }

    public function test_index_with_custom_per_page(): void
    {
        CommitteeDecision::factory(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/committee-decisions?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_index_filter_by_committee_meeting_id(): void
    {
        $meeting1 = CommitteeMeeting::factory()->create();
        $meeting2 = CommitteeMeeting::factory()->create();

        CommitteeDecision::factory(3)->create(['committee_meeting_id' => $meeting1->id]);
        CommitteeDecision::factory(2)->create(['committee_meeting_id' => $meeting2->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-decisions?committee_meeting_id='.$meeting1->id);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_index_filter_by_decision_type(): void
    {
        CommitteeDecision::factory()->approved()->create();
        CommitteeDecision::factory()->denied()->create();
        CommitteeDecision::factory()->waived()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-decisions?decision_type='.DecisionType::APPROVE->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals(DecisionType::APPROVE->value, $response->json('data.data.0.decision_type'));
    }

    public function test_index_filter_by_decision_scope(): void
    {
        $aiModel = AiModel::factory()->create();
        CommitteeDecision::factory()->forModel($aiModel)->create();
        CommitteeDecision::factory()->forUseCase(UseCase::factory()->create())->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-decisions?decision_scope='.DecisionScope::MODEL->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals(DecisionScope::MODEL->value, $response->json('data.data.0.decision_scope'));
    }

    public function test_index_filter_by_vote_method(): void
    {
        CommitteeDecision::factory()->create(['vote_method' => VoteMethod::SIMPLE_MAJORITY->value]);
        CommitteeDecision::factory()->create(['vote_method' => VoteMethod::SUPER_MAJORITY->value]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-decisions?vote_method='.VoteMethod::SIMPLE_MAJORITY->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_index_filter_by_vote_result(): void
    {
        CommitteeDecision::factory()->create(['vote_result' => VoteResult::PASSED->value]);
        CommitteeDecision::factory()->create(['vote_result' => VoteResult::FAILED->value]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-decisions?vote_result='.VoteResult::PASSED->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_index_with_multiple_filters(): void
    {
        $meeting = CommitteeMeeting::factory()->create();

        CommitteeDecision::factory()
            ->for($meeting, 'committeeMeeting')
            ->approved()
            ->create(['vote_method' => VoteMethod::SIMPLE_MAJORITY->value]);

        CommitteeDecision::factory()
            ->for($meeting, 'committeeMeeting')
            ->denied()
            ->create(['vote_method' => VoteMethod::SUPER_MAJORITY->value]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-decisions?committee_meeting_id='.$meeting->id
                .'&decision_type='.DecisionType::APPROVE->value
                .'&vote_method='.VoteMethod::SIMPLE_MAJORITY->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    // Store tests
    public function test_store_creates_committee_decision(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/committee-decisions', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'committee_meeting_id',
                'decision_type',
                'decision_scope',
                'vote_method',
                'vote_result',
                'rationale',
                'owner_team',
            ],
        ]);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Committee decision created successfully', $response->json('message'));
        $this->assertDatabaseHas('committee_decisions', [
            'committee_meeting_id' => $this->meeting->id,
            'decision_type' => DecisionType::APPROVE->value,
            'rationale' => 'Model meets compliance requirements',
        ]);
    }

    public function test_store_with_all_decision_types(): void
    {
        foreach (DecisionType::cases() as $type) {
            $payload = $this->validPayload(['decision_type' => $type->value]);

            $response = $this->actingAs($this->user)->postJson('/api/committee-decisions', $payload);

            $response->assertStatus(201);
            $this->assertEquals($type->value, $response->json('data.decision_type'));
        }
    }

    public function test_store_with_all_vote_methods(): void
    {
        foreach (VoteMethod::cases() as $method) {
            $payload = $this->validPayload(['vote_method' => $method->value]);

            $response = $this->actingAs($this->user)->postJson('/api/committee-decisions', $payload);

            $response->assertStatus(201);
            $this->assertEquals($method->value, $response->json('data.vote_method'));
        }
    }

    public function test_store_with_all_vote_results(): void
    {
        foreach (VoteResult::cases() as $result) {
            $payload = $this->validPayload(['vote_result' => $result->value]);

            $response = $this->actingAs($this->user)->postJson('/api/committee-decisions', $payload);

            $response->assertStatus(201);
            $this->assertEquals($result->value, $response->json('data.vote_result'));
        }
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/committee-decisions', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'committee_meeting_id',
            'decision_type',
            'decision_scope',
            'vote_method',
            'vote_result',
            'rationale',
            'owner_team',
        ]);
    }

    public function test_store_validates_invalid_meeting_id(): void
    {
        $payload = $this->validPayload(['committee_meeting_id' => 9999]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-decisions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['committee_meeting_id']);
    }

    public function test_store_validates_invalid_decision_type(): void
    {
        $payload = $this->validPayload(['decision_type' => 'invalid_type']);

        $response = $this->actingAs($this->user)->postJson('/api/committee-decisions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['decision_type']);
    }

    public function test_store_validates_invalid_vote_method(): void
    {
        $payload = $this->validPayload(['vote_method' => 'invalid_method']);

        $response = $this->actingAs($this->user)->postJson('/api/committee-decisions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vote_method']);
    }

    public function test_store_with_optional_fields(): void
    {
        $aiModel = AiModel::factory()->create();
        $payload = $this->validPayload([
            'ai_model_id' => $aiModel->id,
            'related_ref' => 'REF-001',
            'conditions' => 'Quarterly review required',
            'expiry_date' => now()->addYear()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-decisions', $payload);

        $response->assertStatus(201);
        $this->assertEquals('REF-001', $response->json('data.related_ref'));
        $this->assertEquals('Quarterly review required', $response->json('data.conditions'));
    }

    // Show tests
    public function test_show_returns_committee_decision(): void
    {
        $decision = CommitteeDecision::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/committee-decisions/{$decision->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'committee_meeting_id',
                'decision_type',
                'decision_scope',
                'vote_method',
                'vote_result',
            ],
        ]);
        $this->assertEquals($decision->id, $response->json('data.id'));
    }

    public function test_show_returns_404_for_nonexistent_decision(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/committee-decisions/9999');

        $response->assertStatus(404);
    }

    public function test_show_eager_loads_relationships(): void
    {
        $decision = CommitteeDecision::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/committee-decisions/{$decision->id}");

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.committee_meeting_id'));
    }

    // Update tests
    public function test_update_committee_decision(): void
    {
        $decision = CommitteeDecision::factory()->approved()->create();

        $payload = [
            'decision_type' => DecisionType::DENY->value,
            'vote_result' => VoteResult::FAILED->value,
            'owner_team' => 'ethics_board',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/committee-decisions/{$decision->id}", $payload);

        $response->assertStatus(200);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Committee decision updated successfully', $response->json('message'));
        $this->assertEquals(DecisionType::DENY->value, $response->json('data.decision_type'));
        $this->assertEquals(VoteResult::FAILED->value, $response->json('data.vote_result'));
        $this->assertDatabaseHas('committee_decisions', [
            'id' => $decision->id,
            'decision_type' => DecisionType::DENY->value,
            'owner_team' => 'ethics_board',
        ]);
    }

    public function test_update_partial_fields(): void
    {
        $decision = CommitteeDecision::factory()->create();

        $payload = ['owner_team' => 'compliance_team'];

        $response = $this->actingAs($this->user)->postJson("/api/committee-decisions/{$decision->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('compliance_team', $response->json('data.owner_team'));
        $this->assertEquals($decision->decision_type, $response->json('data.decision_type')); // unchanged
    }

    public function test_update_with_all_decision_types(): void
    {
        foreach (DecisionType::cases() as $type) {
            $decision = CommitteeDecision::factory()->create();
            $payload = ['decision_type' => $type->value];

            $response = $this->actingAs($this->user)->postJson("/api/committee-decisions/{$decision->id}", $payload);

            $response->assertStatus(200);
            $this->assertEquals($type->value, $response->json('data.decision_type'));
        }
    }

    public function test_update_validates_invalid_decision_type(): void
    {
        $decision = CommitteeDecision::factory()->create();

        $payload = ['decision_type' => 'invalid_type'];

        $response = $this->actingAs($this->user)->postJson("/api/committee-decisions/{$decision->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['decision_type']);
    }

    public function test_update_validates_invalid_vote_method(): void
    {
        $decision = CommitteeDecision::factory()->create();

        $payload = ['vote_method' => 'invalid_method'];

        $response = $this->actingAs($this->user)->postJson("/api/committee-decisions/{$decision->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vote_method']);
    }

    public function test_update_returns_404_for_nonexistent_decision(): void
    {
        $payload = ['owner_team' => 'ai_governance'];

        $response = $this->actingAs($this->user)->postJson('/api/committee-decisions/9999', $payload);

        $response->assertStatus(404);
    }

    public function test_destroy_deletes_committee_decision(): void
    {
        $decision = CommitteeDecision::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/committee-decisions/{$decision->id}");

        $response->assertStatus(200);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Committee decision deleted successfully', $response->json('message'));
        $this->assertDatabaseMissing('committee_decisions', ['id' => $decision->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_decision(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/committee-decisions/9999');

        $response->assertStatus(404);
    }
}
