<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Stakeholder;
use App\Models\CommitteeAction;
use App\Models\CommitteeDecision;
use App\Enums\CommitteeAction\Status;
use App\Enums\CommitteeAction\ActionType;
use App\Repositories\CommitteeActionRepository;
use App\Enums\CommitteeAction\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeActionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CommitteeActionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CommitteeActionRepository;
    }

    public function test_get_filtered_returns_paginated_results(): void
    {
        CommitteeAction::factory(25)->create();

        $result = $this->repository->getFilteredCommitteeActions(['per_page' => 10]);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertCount(10, $result->items());
    }

    public function test_get_filtered_with_default_per_page(): void
    {
        CommitteeAction::factory(20)->create();

        $result = $this->repository->getFilteredCommitteeActions([]);

        $this->assertEquals(15, $result->perPage()); // Default per_page
        $this->assertEquals(20, $result->total());
    }

    public function test_get_filtered_eager_loads_relationships(): void
    {
        CommitteeAction::factory()->create();

        $result = $this->repository->getFilteredCommitteeActions([]);

        $this->assertTrue($result->first()->relationLoaded('committeeDecision'));
        $this->assertTrue($result->first()->relationLoaded('assignee'));
    }

    public function test_get_filtered_orders_by_created_at_descending(): void
    {
        $action1 = CommitteeAction::factory()->create();
        sleep(1);
        $action2 = CommitteeAction::factory()->create();

        $result = $this->repository->getFilteredCommitteeActions([]);
        $items = $result->items();

        $this->assertEquals($action2->id, $items[0]->id);
        $this->assertEquals($action1->id, $items[1]->id);
    }

    public function test_filter_by_committee_decision_id(): void
    {
        $decision1 = CommitteeDecision::factory()->create();
        $decision2 = CommitteeDecision::factory()->create();

        CommitteeAction::factory()->forDecision($decision1)->create();
        CommitteeAction::factory()->forDecision($decision2)->create();

        $result = $this->repository->getFilteredCommitteeActions([
            'committee_decision_id' => $decision1->id,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals($decision1->id, $result->first()->committee_decision_id);
    }

    public function test_filter_by_action_type(): void
    {
        CommitteeAction::factory()->implementChange()->create();
        CommitteeAction::factory()->collectEvidence()->create();
        CommitteeAction::factory()->updatePolicy()->create();

        $result = $this->repository->getFilteredCommitteeActions([
            'action_type' => ActionType::IMPLEMENT_CHANGE->value,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals(ActionType::IMPLEMENT_CHANGE->value, $result->first()->action_type);
    }

    public function test_filter_by_assignee_id(): void
    {
        $stakeholder1 = Stakeholder::factory()->create();
        $stakeholder2 = Stakeholder::factory()->create();

        CommitteeAction::factory()->withAssignee($stakeholder1)->create();
        CommitteeAction::factory()->withAssignee($stakeholder2)->create();

        $result = $this->repository->getFilteredCommitteeActions([
            'assignee_id' => $stakeholder1->id,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals($stakeholder1->id, $result->first()->assignee_id);
    }

    public function test_filter_by_status(): void
    {
        CommitteeAction::factory()->statusNew()->create();
        CommitteeAction::factory()->inProgress()->create();
        CommitteeAction::factory()->completed()->create();

        $result = $this->repository->getFilteredCommitteeActions([
            'status' => Status::NEW->value,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals(Status::NEW->value, $result->first()->status);
    }

    public function test_filter_by_verification_result(): void
    {
        CommitteeAction::factory()->create(['verification_result' => VerificationResult::PASSED->value]);
        CommitteeAction::factory()->create(['verification_result' => VerificationResult::FAILED->value]);

        $result = $this->repository->getFilteredCommitteeActions([
            'verification_result' => VerificationResult::PASSED->value,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals(VerificationResult::PASSED->value, $result->first()->verification_result);
    }

    public function test_filter_by_multiple_criteria(): void
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

        $result = $this->repository->getFilteredCommitteeActions([
            'committee_decision_id' => $decision->id,
            'assignee_id' => $stakeholder->id,
            'action_type' => ActionType::IMPLEMENT_CHANGE->value,
            'status' => Status::NEW->value,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals(ActionType::IMPLEMENT_CHANGE->value, $result->first()->action_type);
    }

    public function test_handles_all_action_types(): void
    {
        foreach (ActionType::cases() as $type) {
            CommitteeAction::factory()->create(['action_type' => $type->value]);
        }

        foreach (ActionType::cases() as $type) {
            $result = $this->repository->getFilteredCommitteeActions([
                'action_type' => $type->value,
            ]);
            $this->assertCount(1, $result->items());
            $this->assertEquals($type->value, $result->first()->action_type);
        }
    }

    public function test_handles_all_statuses(): void
    {
        foreach (Status::cases() as $status) {
            CommitteeAction::factory()->create(['status' => $status->value]);
        }

        foreach (Status::cases() as $status) {
            $result = $this->repository->getFilteredCommitteeActions([
                'status' => $status->value,
            ]);
            $this->assertCount(1, $result->items());
            $this->assertEquals($status->value, $result->first()->status);
        }
    }

    public function test_handles_all_verification_results(): void
    {
        foreach (VerificationResult::cases() as $result) {
            CommitteeAction::factory()->create(['verification_result' => $result->value]);
        }

        foreach (VerificationResult::cases() as $result) {
            $queryResult = $this->repository->getFilteredCommitteeActions([
                'verification_result' => $result->value,
            ]);
            $this->assertCount(1, $queryResult->items());
            $this->assertEquals($result->value, $queryResult->first()->verification_result);
        }
    }

    public function test_create_committee_action_with_required_fields(): void
    {
        $decision = CommitteeDecision::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'committee_decision_id' => $decision->id,
            'title' => 'Implement AI Controls',
            'action_type' => ActionType::IMPLEMENT_CHANGE->value,
            'assignee_id' => $stakeholder->id,
            'due_date' => '2025-02-15',
            'status' => Status::NEW->value,
            'verification_result' => VerificationResult::PENDING->value,
        ];

        $action = $this->repository->createCommitteeAction($data);

        $this->assertInstanceOf(CommitteeAction::class, $action);
        $this->assertDatabaseHas('committee_actions', [
            'id' => $action->id,
            'committee_decision_id' => $decision->id,
            'title' => 'Implement AI Controls',
            'action_type' => ActionType::IMPLEMENT_CHANGE->value,
        ]);
    }

    public function test_create_committee_action_with_all_fields(): void
    {
        $decision = CommitteeDecision::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'committee_decision_id' => $decision->id,
            'title' => 'Collect Evidence',
            'action_type' => ActionType::COLLECT_EVIDENCE->value,
            'assignee_id' => $stakeholder->id,
            'due_date' => '2025-02-15',
            'status' => Status::IN_PROGRESS->value,
            'verification_result' => VerificationResult::PENDING->value,
            'evidence_link' => 'https://example.com/evidence',
            'notes' => 'Review all documentation',
            'closed_at' => null,
        ];

        $action = $this->repository->createCommitteeAction($data);

        $this->assertDatabaseHas('committee_actions', [
            'id' => $action->id,
            'committee_decision_id' => $decision->id,
            'title' => 'Collect Evidence',
            'evidence_link' => 'https://example.com/evidence',
            'notes' => 'Review all documentation',
        ]);
    }

    public function test_update_committee_action(): void
    {
        $action = CommitteeAction::factory()->statusNew()->create();
        $newStakeholder = Stakeholder::factory()->create();

        $data = [
            'title' => 'Updated Action Title',
            'status' => Status::IN_PROGRESS->value,
            'assignee_id' => $newStakeholder->id,
        ];

        $updated = $this->repository->updateCommitteeAction($action, $data);

        $this->assertInstanceOf(CommitteeAction::class, $updated);
        $this->assertEquals('Updated Action Title', $updated->title);
        $this->assertEquals(Status::IN_PROGRESS->value, $updated->status);
        $this->assertEquals($newStakeholder->id, $updated->assignee_id);
    }

    public function test_update_committee_action_eager_loads_relationships(): void
    {
        $action = CommitteeAction::factory()->create();

        $updated = $this->repository->updateCommitteeAction($action, []);

        $this->assertTrue($updated->relationLoaded('committeeDecision'));
        $this->assertTrue($updated->relationLoaded('assignee'));
    }

    public function test_delete_committee_action(): void
    {
        $action = CommitteeAction::factory()->create();
        $id = $action->id;

        $result = $this->repository->deleteCommitteeAction($action);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('committee_actions', ['id' => $id]);
    }

    public function test_delete_returns_true(): void
    {
        $action = CommitteeAction::factory()->create();

        $result = $this->repository->deleteCommitteeAction($action);

        $this->assertTrue($result);
    }

    // Relationship tests
    public function test_action_has_committee_decision(): void
    {
        $decision = CommitteeDecision::factory()->create();
        $action = CommitteeAction::factory()->forDecision($decision)->create();

        $result = $this->repository->getFilteredCommitteeActions([]);

        $this->assertNotNull($result->first()->committee_decision_id);
        $this->assertEquals($decision->id, $result->first()->committee_decision_id);
    }

    public function test_action_has_assignee(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $action = CommitteeAction::factory()->withAssignee($stakeholder)->create();

        $result = $this->repository->getFilteredCommitteeActions([]);

        $this->assertNotNull($result->first()->assignee_id);
        $this->assertEquals($stakeholder->id, $result->first()->assignee_id);
    }

    // Optional fields tests
    public function test_action_with_optional_fields(): void
    {
        $data = [
            'committee_decision_id' => CommitteeDecision::factory()->create()->id,
            'title' => 'Test Action',
            'action_type' => ActionType::IMPLEMENT_CHANGE->value,
            'assignee_id' => Stakeholder::factory()->create()->id,
            'due_date' => '2025-02-15',
            'status' => Status::NEW->value,
            'verification_result' => VerificationResult::PENDING->value,
            'evidence_link' => null,
            'notes' => null,
            'closed_at' => null,
        ];

        $action = $this->repository->createCommitteeAction($data);

        $this->assertNull($action->evidence_link);
        $this->assertNull($action->notes);
        $this->assertNull($action->closed_at);
    }

    // State-based filtering tests
    public function test_filter_by_completed_actions(): void
    {
        CommitteeAction::factory()->completed()->count(3)->create();
        CommitteeAction::factory()->statusNew()->count(2)->create();
        CommitteeAction::factory()->inProgress()->count(1)->create();

        $result = $this->repository->getFilteredCommitteeActions([
            'status' => Status::COMPLETED->value,
        ]);

        $this->assertCount(3, $result->items());
        $this->assertTrue($result->first()->closed_at !== null);
    }

    public function test_filter_by_new_actions(): void
    {
        CommitteeAction::factory()->statusNew()->count(5)->create();
        CommitteeAction::factory()->inProgress()->count(3)->create();

        $result = $this->repository->getFilteredCommitteeActions([
            'status' => Status::NEW->value,
        ]);

        $this->assertCount(5, $result->items());
        $this->assertEquals(VerificationResult::PENDING->value, $result->first()->verification_result);
    }
}
