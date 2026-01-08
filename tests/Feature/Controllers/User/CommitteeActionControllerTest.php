<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Stakeholder;
use App\Models\CommitteeAction;
use App\Models\CommitteeDecision;
use App\Enums\CommitteeAction\Status;
use App\Enums\CommitteeAction\ActionType;
use App\Enums\CommitteeAction\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeActionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected CommitteeDecision $decision;

    protected Stakeholder $stakeholder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->decision = CommitteeDecision::factory()->create();
        $this->stakeholder = Stakeholder::factory()->create();
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'committee_decision_id' => $this->decision->id,
            'title' => 'Implement AI Controls',
            'action_type' => ActionType::IMPLEMENT_CHANGE->value,
            'assignee_id' => $this->stakeholder->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => Status::NEW->value,
            'verification_result' => VerificationResult::PENDING->value,
            'evidence_link' => null,
            'notes' => null,
            'closed_at' => null,
        ], $overrides);
    }

    // Index tests
    public function test_index_returns_paginated_committee_actions(): void
    {
        CommitteeAction::factory(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/committee-actions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'committee_decision_id',
                        'title',
                        'action_type',
                        'assignee_id',
                        'due_date',
                        'status',
                        'verification_result',
                        'evidence_link',
                        'notes',
                        'closed_at',
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
        CommitteeAction::factory(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/committee-actions?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_index_filter_by_committee_decision_id(): void
    {
        $decision1 = CommitteeDecision::factory()->create();
        $decision2 = CommitteeDecision::factory()->create();

        CommitteeAction::factory(3)->forDecision($decision1)->create();
        CommitteeAction::factory(2)->forDecision($decision2)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-actions?committee_decision_id='.$decision1->id);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_index_filter_by_action_type(): void
    {
        CommitteeAction::factory()->implementChange()->create();
        CommitteeAction::factory()->collectEvidence()->create();
        CommitteeAction::factory()->updatePolicy()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-actions?action_type='.ActionType::IMPLEMENT_CHANGE->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals(ActionType::IMPLEMENT_CHANGE->value, $response->json('data.data.0.action_type'));
    }

    public function test_index_filter_by_assignee_id(): void
    {
        $stakeholder1 = Stakeholder::factory()->create();
        $stakeholder2 = Stakeholder::factory()->create();

        CommitteeAction::factory(2)->withAssignee($stakeholder1)->create();
        CommitteeAction::factory(3)->withAssignee($stakeholder2)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-actions?assignee_id='.$stakeholder1->id);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_index_filter_by_status(): void
    {
        CommitteeAction::factory(2)->statusNew()->create();
        CommitteeAction::factory(3)->inProgress()->create();
        CommitteeAction::factory(1)->completed()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-actions?status='.Status::IN_PROGRESS->value);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
        $this->assertEquals(Status::IN_PROGRESS->value, $response->json('data.data.0.status'));
    }

    public function test_index_filter_by_verification_result(): void
    {
        CommitteeAction::factory()->create(['verification_result' => VerificationResult::PENDING->value]);
        CommitteeAction::factory()->create(['verification_result' => VerificationResult::PASSED->value]);
        CommitteeAction::factory()->create(['verification_result' => VerificationResult::FAILED->value]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-actions?verification_result='.VerificationResult::PASSED->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals(VerificationResult::PASSED->value, $response->json('data.data.0.verification_result'));
    }

    public function test_index_with_multiple_filters(): void
    {
        $decision = CommitteeDecision::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        CommitteeAction::factory()
            ->forDecision($decision)
            ->withAssignee($stakeholder)
            ->implementChange()
            ->statusNew()
            ->create();

        CommitteeAction::factory()
            ->forDecision($decision)
            ->withAssignee($stakeholder)
            ->collectEvidence()
            ->inProgress()
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-actions?committee_decision_id='.$decision->id
                .'&assignee_id='.$stakeholder->id
                .'&action_type='.ActionType::IMPLEMENT_CHANGE->value
                .'&status='.Status::NEW->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    // Store tests
    public function test_store_creates_committee_action(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'committee_decision_id',
                'title',
                'action_type',
                'assignee_id',
                'due_date',
                'status',
                'verification_result',
                'evidence_link',
                'notes',
                'closed_at',
            ],
        ]);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Committee action created successfully', $response->json('message'));
        $this->assertDatabaseHas('committee_actions', [
            'committee_decision_id' => $this->decision->id,
            'title' => 'Implement AI Controls',
            'action_type' => ActionType::IMPLEMENT_CHANGE->value,
        ]);
    }

    public function test_store_with_all_action_types(): void
    {
        foreach (ActionType::cases() as $type) {
            $payload = $this->validPayload(['action_type' => $type->value]);

            $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

            $response->assertStatus(201);
            $this->assertEquals($type->value, $response->json('data.action_type'));
        }
    }

    public function test_store_with_all_statuses(): void
    {
        foreach (Status::cases() as $status) {
            $payload = $this->validPayload(['status' => $status->value]);

            $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

            $response->assertStatus(201);
            $this->assertEquals($status->value, $response->json('data.status'));
        }
    }

    public function test_store_with_all_verification_results(): void
    {
        foreach (VerificationResult::cases() as $result) {
            $payload = $this->validPayload(['verification_result' => $result->value]);

            $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

            $response->assertStatus(201);
            $this->assertEquals($result->value, $response->json('data.verification_result'));
        }
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'committee_decision_id',
            'title',
            'action_type',
            'assignee_id',
            'due_date',
            'status',
            'verification_result',
        ]);
    }

    public function test_store_validates_invalid_decision_id(): void
    {
        $payload = $this->validPayload(['committee_decision_id' => 9999]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['committee_decision_id']);
    }

    public function test_store_validates_invalid_assignee_id(): void
    {
        $payload = $this->validPayload(['assignee_id' => 9999]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['assignee_id']);
    }

    public function test_store_validates_invalid_action_type(): void
    {
        $payload = $this->validPayload(['action_type' => 'invalid_type']);

        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['action_type']);
    }

    public function test_store_validates_invalid_status(): void
    {
        $payload = $this->validPayload(['status' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function test_store_validates_invalid_verification_result(): void
    {
        $payload = $this->validPayload(['verification_result' => 'invalid_result']);

        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['verification_result']);
    }

    public function test_store_validates_invalid_email_for_evidence_link(): void
    {
        $payload = $this->validPayload(['evidence_link' => 'not-a-url']);

        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['evidence_link']);
    }

    public function test_store_validates_due_date_after_today(): void
    {
        $payload = $this->validPayload(['due_date' => now()->subDays(1)->toDateString()]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['due_date']);
    }

    public function test_store_with_optional_fields(): void
    {
        $payload = $this->validPayload([
            'evidence_link' => 'https://example.com/evidence',
            'notes' => 'This is a test note',
            'closed_at' => null,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-actions', $payload);

        $response->assertStatus(201);
        $this->assertEquals('https://example.com/evidence', $response->json('data.evidence_link'));
        $this->assertEquals('This is a test note', $response->json('data.notes'));
    }

    // Show tests
    public function test_show_returns_committee_action(): void
    {
        $action = CommitteeAction::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/committee-actions/{$action->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'committee_decision_id',
                'title',
                'action_type',
                'assignee_id',
                'due_date',
                'status',
                'verification_result',
                'evidence_link',
                'notes',
                'closed_at',
                'created_at',
                'updated_at',
            ],
        ]);
        $this->assertEquals($action->id, $response->json('data.id'));
    }

    public function test_show_returns_404_for_nonexistent_action(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/committee-actions/9999');

        $response->assertStatus(404);
    }

    public function test_show_eager_loads_relationships(): void
    {
        $action = CommitteeAction::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/committee-actions/{$action->id}");

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.committee_decision_id'));
        $this->assertNotNull($response->json('data.assignee_id'));
    }

    // Update tests
    public function test_update_committee_action(): void
    {
        $action = CommitteeAction::factory()->statusNew()->create();

        $payload = [
            'title' => 'Updated Action Title',
            'status' => Status::IN_PROGRESS->value,
            'verification_result' => VerificationResult::PENDING->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/committee-actions/{$action->id}", $payload);

        $response->assertStatus(200);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Committee action updated successfully', $response->json('message'));
        $this->assertEquals('Updated Action Title', $response->json('data.title'));
        $this->assertEquals(Status::IN_PROGRESS->value, $response->json('data.status'));
        $this->assertDatabaseHas('committee_actions', [
            'id' => $action->id,
            'title' => 'Updated Action Title',
            'status' => Status::IN_PROGRESS->value,
        ]);
    }

    public function test_update_partial_fields(): void
    {
        $action = CommitteeAction::factory()->statusNew()->create();

        $payload = ['status' => Status::COMPLETED->value];

        $response = $this->actingAs($this->user)->postJson("/api/committee-actions/{$action->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals(Status::COMPLETED->value, $response->json('data.status'));
        $this->assertEquals($action->title, $response->json('data.title')); // unchanged
    }

    public function test_update_with_all_statuses(): void
    {
        foreach (Status::cases() as $status) {
            $action = CommitteeAction::factory()->create();
            $payload = ['status' => $status->value];

            $response = $this->actingAs($this->user)->postJson("/api/committee-actions/{$action->id}", $payload);

            $response->assertStatus(200);
            $this->assertEquals($status->value, $response->json('data.status'));
        }
    }

    public function test_update_validates_invalid_action_type(): void
    {
        $action = CommitteeAction::factory()->create();

        $payload = ['action_type' => 'invalid_type'];

        $response = $this->actingAs($this->user)->postJson("/api/committee-actions/{$action->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['action_type']);
    }

    public function test_update_validates_invalid_status(): void
    {
        $action = CommitteeAction::factory()->create();

        $payload = ['status' => 'invalid_status'];

        $response = $this->actingAs($this->user)->postJson("/api/committee-actions/{$action->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    // Delete tests
    public function test_destroy_deletes_committee_action(): void
    {
        $action = CommitteeAction::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/committee-actions/{$action->id}");

        $response->assertStatus(200);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Committee action deleted successfully', $response->json('message'));
        $this->assertDatabaseMissing('committee_actions', ['id' => $action->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_action(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/committee-actions/9999');

        $response->assertStatus(404);
    }
}
