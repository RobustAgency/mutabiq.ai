<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\AiModel;
use App\Models\Control;
use App\Models\UseCase;
use App\Models\CommitteeMeeting;
use App\Models\CommitteeDecision;
use App\Enums\CommitteeDecision\VoteMethod;
use App\Enums\CommitteeDecision\VoteResult;
use App\Enums\CommitteeDecision\DecisionType;
use App\Enums\CommitteeDecision\DecisionScope;
use App\Repositories\CommitteeDecisionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeDecisionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CommitteeDecisionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CommitteeDecisionRepository;
    }

    // Pagination tests
    public function test_get_filtered_returns_paginated_results(): void
    {
        CommitteeDecision::factory(25)->create();

        $result = $this->repository->getFilteredCommitteeDecisions(['per_page' => 10]);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertCount(10, $result->items());
    }

    public function test_get_filtered_with_default_per_page(): void
    {
        CommitteeDecision::factory(20)->create();

        $result = $this->repository->getFilteredCommitteeDecisions([]);

        $this->assertEquals(15, $result->perPage()); // Default per_page
        $this->assertEquals(20, $result->total());
    }

    public function test_get_filtered_eager_loads_relationships(): void
    {
        CommitteeDecision::factory()->create();

        $result = $this->repository->getFilteredCommitteeDecisions([]);

        $this->assertTrue($result->first()->relationLoaded('committeeMeeting'));
    }

    public function test_get_filtered_orders_by_created_at_descending(): void
    {
        $decision1 = CommitteeDecision::factory()->create();
        sleep(1);
        $decision2 = CommitteeDecision::factory()->create();

        $result = $this->repository->getFilteredCommitteeDecisions([]);
        $items = $result->items();

        $this->assertEquals($decision2->id, $items[0]->id);
        $this->assertEquals($decision1->id, $items[1]->id);
    }

    // Filter tests
    public function test_filter_by_committee_meeting_id(): void
    {
        $meeting1 = CommitteeMeeting::factory()->create();
        $meeting2 = CommitteeMeeting::factory()->create();

        CommitteeDecision::factory()->create(['committee_meeting_id' => $meeting1->id]);
        CommitteeDecision::factory()->create(['committee_meeting_id' => $meeting2->id]);

        $result = $this->repository->getFilteredCommitteeDecisions([
            'committee_meeting_id' => $meeting1->id,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals($meeting1->id, $result->first()->committee_meeting_id);
    }

    public function test_filter_by_decision_type(): void
    {
        CommitteeDecision::factory()->create(['decision_type' => DecisionType::APPROVE->value]);
        CommitteeDecision::factory()->create(['decision_type' => DecisionType::DENY->value]);
        CommitteeDecision::factory()->create(['decision_type' => DecisionType::WAIVE->value]);

        $result = $this->repository->getFilteredCommitteeDecisions([
            'decision_type' => DecisionType::APPROVE->value,
        ]);

        $this->assertCount(1, $result->items());
    }

    public function test_filter_by_vote_method(): void
    {
        CommitteeDecision::factory()->create(['vote_method' => VoteMethod::SIMPLE_MAJORITY->value]);
        CommitteeDecision::factory()->create(['vote_method' => VoteMethod::SUPER_MAJORITY->value]);

        $result = $this->repository->getFilteredCommitteeDecisions([
            'vote_method' => VoteMethod::SIMPLE_MAJORITY->value,
        ]);

        $this->assertCount(1, $result->items());
    }

    public function test_filter_by_vote_result(): void
    {
        CommitteeDecision::factory()->create(['vote_result' => VoteResult::PASSED->value]);
        CommitteeDecision::factory()->create(['vote_result' => VoteResult::FAILED->value]);

        $result = $this->repository->getFilteredCommitteeDecisions([
            'vote_result' => VoteResult::PASSED->value,
        ]);

        $this->assertCount(1, $result->items());
    }

    // Combined filters
    public function test_filter_by_multiple_criteria(): void
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

        $result = $this->repository->getFilteredCommitteeDecisions([
            'committee_meeting_id' => $meeting->id,
            'decision_type' => DecisionType::APPROVE->value,
            'vote_method' => VoteMethod::SIMPLE_MAJORITY->value,
        ]);

        $this->assertCount(1, $result->items());
    }

    // Create tests
    public function test_create_committee_decision_with_required_fields(): void
    {
        $meeting = CommitteeMeeting::factory()->create();

        $data = [
            'committee_meeting_id' => $meeting->id,
            'decision_type' => DecisionType::APPROVE->value,
            'decision_scope' => DecisionScope::MODEL->value,
            'vote_method' => VoteMethod::SIMPLE_MAJORITY->value,
            'vote_result' => VoteResult::PASSED->value,
            'rationale' => 'Meets all criteria',
            'owner_team' => 'ai_governance',
        ];

        $decision = $this->repository->createCommitteeDecision($data);

        $this->assertInstanceOf(CommitteeDecision::class, $decision);
        $this->assertDatabaseHas('committee_decisions', [
            'id' => $decision->id,
            'committee_meeting_id' => $meeting->id,
            'decision_type' => DecisionType::APPROVE->value,
        ]);
    }

    public function test_create_committee_decision_with_all_fields(): void
    {
        $meeting = CommitteeMeeting::factory()->create();
        $aiModel = AiModel::factory()->create();
        $useCase = UseCase::factory()->create();
        $control = Control::factory()->create();

        $data = [
            'committee_meeting_id' => $meeting->id,
            'decision_type' => DecisionType::APPROVE->value,
            'decision_scope' => DecisionScope::MODEL->value,
            'ai_model_id' => $aiModel->id,
            'use_case_id' => $useCase->id,
            'control_id' => $control->id,
            'related_ref' => 'REF-001',
            'rationale' => 'Model meets compliance requirements',
            'conditions' => 'Quarterly review required',
            'expiry_date' => '2025-12-31',
            'vote_method' => VoteMethod::SIMPLE_MAJORITY->value,
            'vote_result' => VoteResult::PASSED->value,
            'owner_team' => 'ai_governance',
        ];

        $decision = $this->repository->createCommitteeDecision($data);

        $this->assertDatabaseHas('committee_decisions', [
            'id' => $decision->id,
            'committee_meeting_id' => $meeting->id,
            'decision_type' => DecisionType::APPROVE->value,
            'related_ref' => 'REF-001',
            'owner_team' => 'ai_governance',
        ]);
    }

    // Update tests
    public function test_update_committee_decision(): void
    {
        $decision = CommitteeDecision::factory()->create();
        $newMeeting = CommitteeMeeting::factory()->create();

        $data = [
            'committee_meeting_id' => $newMeeting->id,
            'decision_type' => DecisionType::DENY->value,
            'vote_result' => VoteResult::FAILED->value,
        ];

        $updated = $this->repository->updateCommitteeDecision($decision, $data);

        $this->assertInstanceOf(CommitteeDecision::class, $updated);
        $this->assertEquals($newMeeting->id, $updated->committee_meeting_id);
        $this->assertEquals(DecisionType::DENY->value, $updated->decision_type);
        $this->assertEquals(VoteResult::FAILED->value, $updated->vote_result);
    }

    public function test_update_committee_decision_eager_loads_relationships(): void
    {
        $decision = CommitteeDecision::factory()->create();

        $updated = $this->repository->updateCommitteeDecision($decision, []);

        $this->assertTrue($updated->relationLoaded('committeeMeeting'));
        $this->assertTrue($updated->relationLoaded('aiModel'));
        $this->assertTrue($updated->relationLoaded('useCase'));
        $this->assertTrue($updated->relationLoaded('control'));
    }

    public function test_update_committee_decision_returns_fresh_instance(): void
    {
        $decision = CommitteeDecision::factory()->create(['owner_team' => 'ai_governance']);

        $data = ['owner_team' => 'ethics_board'];
        $updated = $this->repository->updateCommitteeDecision($decision, $data);

        $this->assertEquals('ethics_board', $updated->owner_team);
    }

    // Delete tests
    public function test_delete_committee_decision(): void
    {
        $decision = CommitteeDecision::factory()->create();
        $id = $decision->id;

        $result = $this->repository->deleteCommitteeDecision($decision);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('committee_decisions', ['id' => $id]);
    }

    public function test_delete_returns_true(): void
    {
        $decision = CommitteeDecision::factory()->create();

        $result = $this->repository->deleteCommitteeDecision($decision);

        $this->assertTrue($result);
    }

    // Relationship and field tests
    public function test_model_scope_decision_has_ai_model(): void
    {
        $aiModel = AiModel::factory()->create();
        $decision = CommitteeDecision::factory()->forModel($aiModel)->create();

        $result = $this->repository->getFilteredCommitteeDecisions([
            'decision_scope' => DecisionScope::MODEL->value,
        ]);

        $this->assertNotNull($result->first()->ai_model_id);
        $this->assertEquals($aiModel->id, $result->first()->ai_model_id);
        $this->assertNull($result->first()->use_case_id);
        $this->assertNull($result->first()->control_id);
    }

    public function test_use_case_scope_decision_has_use_case(): void
    {
        $useCase = UseCase::factory()->create();
        $decision = CommitteeDecision::factory()->forUseCase($useCase)->create();

        $result = $this->repository->getFilteredCommitteeDecisions([
            'decision_scope' => DecisionScope::USE_CASE->value,
        ]);

        $this->assertNotNull($result->first()->use_case_id);
        $this->assertEquals($useCase->id, $result->first()->use_case_id);
        $this->assertNull($result->first()->ai_model_id);
        $this->assertNull($result->first()->control_id);
    }

    public function test_control_scope_decision_has_control(): void
    {
        $control = Control::factory()->create();
        $decision = CommitteeDecision::factory()->forControl($control)->create();

        $result = $this->repository->getFilteredCommitteeDecisions([
            'decision_scope' => DecisionScope::CONTROL->value,
        ]);

        $this->assertNotNull($result->first()->control_id);
        $this->assertEquals($control->id, $result->first()->control_id);
        $this->assertNull($result->first()->ai_model_id);
        $this->assertNull($result->first()->use_case_id);
    }
}
