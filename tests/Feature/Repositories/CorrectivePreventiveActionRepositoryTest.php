<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\AiModel;
use App\Models\Dataset;
use App\Models\Organization;
use App\Models\CorrectivePreventiveAction;
use App\Enums\CorrectivePreventiveAction\Status;
use App\Enums\CorrectivePreventiveAction\CapaType;
use App\Enums\CorrectivePreventiveAction\Priority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\CorrectivePreventiveAction\OwnerTeam;
use App\Enums\CorrectivePreventiveAction\SourceType;
use App\Repositories\CorrectivePreventiveActionRepository;
use App\Enums\CorrectivePreventiveAction\VerificationResult;

class CorrectivePreventiveActionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CorrectivePreventiveActionRepository $repository;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CorrectivePreventiveActionRepository;
        $this->organization = Organization::factory()->create();
    }

    public function test_paginate_returns_paginated_corrective_preventive_actions(): void
    {
        CorrectivePreventiveAction::factory()->count(15)->create();

        $result = $this->repository->getFilteredCorrectivePreventiveActions(['per_page' => 10]);
        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_paginate_with_default_per_page(): void
    {
        CorrectivePreventiveAction::factory()->count(5)->create();

        $result = $this->repository->getFilteredCorrectivePreventiveActions();

        $this->assertCount(5, $result->items());
    }

    public function test_create_stores_corrective_preventive_action_with_required_fields(): void
    {
        $model = AiModel::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-12345',
            'ai_model_id' => $model->id,
            'title' => 'Test CAPA',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::HIGH->value,
            'actions' => 'Take immediate action',
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7),
            'status' => Status::NEW->value,
            'approved_at' => now(),
        ];

        $capa = $this->repository->createCorrectivePreventiveAction($data);

        $this->assertInstanceOf(CorrectivePreventiveAction::class, $capa);
        $this->assertEquals('Test CAPA', $capa->title);
        $this->assertEquals(CapaType::CORRECTIVE->value, $capa->capa_type);
        $this->assertEquals(Priority::HIGH->value, $capa->priority);

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'title' => 'Test CAPA',
            'capa_type' => CapaType::CORRECTIVE->value,
        ]);
    }

    public function test_create_stores_corrective_preventive_action_with_all_fields(): void
    {
        $model = AiModel::factory()->create();
        $dataset = Dataset::factory()->create();
        $dueDate = now()->addDays(14);
        $reviewDate = now()->addDays(30);

        $data = [
            'organization_id' => $this->organization->id,
            'source_type' => SourceType::RCA->value,
            'source_reference' => 'RCA-67890',
            'ai_model_id' => $model->id,
            'dataset_id' => $dataset->id,
            'title' => 'Comprehensive CAPA',
            'capa_type' => CapaType::BOTH->value,
            'priority' => Priority::CRITICAL->value,
            'root_cause' => 'Insufficient model validation',
            'actions' => 'Implement automated testing, Add monitoring, Update documentation',
            'owner_team' => OwnerTeam::DATA_GOVERNANCE->value,
            'assignee' => 'Jane Doe',
            'due_date' => $dueDate,
            'status' => Status::IN_PROGRESS->value,
            'success_criteria' => 'Model accuracy > 95%',
            'linked_training' => 'Model Validation Best Practices',
            'estimated_cost' => 5000.00,
            'effectiveness_review_date' => $reviewDate,
            'verification_result' => null,
            'evidence_link' => null,
        ];

        $capa = $this->repository->createCorrectivePreventiveAction($data);

        $this->assertEquals('Comprehensive CAPA', $capa->title);
        $this->assertEquals(CapaType::BOTH->value, $capa->capa_type);
        $this->assertEquals('Jane Doe', $capa->assignee);
        $this->assertEquals('Insufficient model validation', $capa->root_cause);
        $this->assertEquals(Status::IN_PROGRESS->value, $capa->status);
        $this->assertEquals('Model accuracy > 95%', $capa->success_criteria);
        $this->assertEquals('Model Validation Best Practices', $capa->linked_training);
        $this->assertEquals(5000.00, $capa->estimated_cost);
    }

    public function test_create_handles_all_source_types(): void
    {
        foreach (SourceType::cases() as $sourceType) {
            $data = [
                'organization_id' => $this->organization->id,
                'source_type' => $sourceType->value,
                'source_reference' => "REF-{$sourceType->name}",
                'title' => "Action from {$sourceType->name}",
                'capa_type' => CapaType::CORRECTIVE->value,
                'priority' => Priority::MEDIUM->value,
                'actions' => 'Take action',
                'owner_team' => OwnerTeam::ML_ENGINEERING->value,
                'due_date' => now()->addDays(7),
                'status' => Status::NEW->value,
            ];

            $capa = $this->repository->createCorrectivePreventiveAction($data);
            $this->assertEquals($sourceType->value, $capa->source_type);
        }
    }

    public function test_create_handles_all_priorities(): void
    {
        foreach (Priority::cases() as $priority) {
            $data = [
                'organization_id' => $this->organization->id,
                'source_type' => SourceType::INCIDENT->value,
                'source_reference' => 'INC-123',
                'title' => "Action with {$priority->name} priority",
                'capa_type' => CapaType::CORRECTIVE->value,
                'priority' => $priority->value,
                'actions' => 'Take action',
                'owner_team' => OwnerTeam::ML_ENGINEERING->value,
                'due_date' => now()->addDays(7),
                'status' => Status::NEW->value,
            ];

            $capa = $this->repository->createCorrectivePreventiveAction($data);
            $this->assertEquals($priority->value, $capa->priority);
        }
    }

    public function test_create_handles_all_owner_teams(): void
    {
        foreach (OwnerTeam::cases() as $team) {
            $data = [
                'organization_id' => $this->organization->id,
                'source_type' => SourceType::INCIDENT->value,
                'source_reference' => 'INC-123',
                'title' => "Action for {$team->name}",
                'capa_type' => CapaType::CORRECTIVE->value,
                'priority' => Priority::MEDIUM->value,
                'actions' => 'Take action',
                'owner_team' => $team->value,
                'due_date' => now()->addDays(7),
                'status' => Status::NEW->value,
            ];

            $capa = $this->repository->createCorrectivePreventiveAction($data);
            $this->assertEquals($team->value, $capa->owner_team);
        }
    }

    public function test_create_handles_all_statuses(): void
    {
        foreach (Status::cases() as $status) {
            $data = [
                'organization_id' => $this->organization->id,
                'source_type' => SourceType::INCIDENT->value,
                'source_reference' => 'INC-123',
                'title' => "Action with {$status->name} status",
                'capa_type' => CapaType::CORRECTIVE->value,
                'priority' => Priority::MEDIUM->value,
                'actions' => 'Take action',
                'owner_team' => OwnerTeam::ML_ENGINEERING->value,
                'due_date' => now()->addDays(7),
                'status' => $status->value,
            ];

            // Add verification result when status is closed
            if ($status === Status::CLOSED) {
                $data['verification_result'] = VerificationResult::VERIFIED_EFFECTIVE->value;
            }

            $capa = $this->repository->createCorrectivePreventiveAction($data);
            $this->assertEquals($status->value, $capa->status);
        }
    }

    public function test_find_by_id_returns_corrective_preventive_action(): void
    {
        $created = CorrectivePreventiveAction::factory()->create();

        $capa = $this->repository->getCorrectivePreventiveActionById($created);

        $this->assertInstanceOf(CorrectivePreventiveAction::class, $capa);
        $this->assertEquals($created->id, $capa->id);
    }

    public function test_update_modifies_corrective_preventive_action(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => Status::NEW->value,
            'priority' => Priority::MEDIUM->value,
            'assignee' => null,
        ]);

        $updateData = [
            'status' => Status::IN_PROGRESS->value,
            'priority' => Priority::HIGH->value,
            'assignee' => 'John Smith',
        ];

        $updated = $this->repository->updateCorrectivePreventiveAction($capa, $updateData);

        $this->assertEquals(Status::IN_PROGRESS->value, $updated->status);
        $this->assertEquals(Priority::HIGH->value, $updated->priority);
        $this->assertEquals('John Smith', $updated->assignee);
    }

    public function test_update_can_close_action(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => Status::PENDING_VERIFICATION->value,
            'verification_result' => null,
        ]);

        $updateData = [
            'status' => Status::CLOSED->value,
            'verification_result' => VerificationResult::VERIFIED_EFFECTIVE->value,
            'evidence_link' => 'https://example.com/evidence',
            'effectiveness_review_date' => now(),
        ];

        $updated = $this->repository->updateCorrectivePreventiveAction($capa, $updateData);

        $this->assertEquals(Status::CLOSED->value, $updated->status);
        $this->assertEquals(VerificationResult::VERIFIED_EFFECTIVE->value, $updated->verification_result);
        $this->assertEquals('https://example.com/evidence', $updated->evidence_link);
        $this->assertNotNull($updated->effectiveness_review_date);
    }

    public function test_update_modifies_only_provided_fields(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'title' => 'Original title',
            'priority' => Priority::LOW->value,
            'assignee' => 'Original assignee',
        ]);

        $updateData = [
            'assignee' => 'New assignee',
        ];

        $updated = $this->repository->updateCorrectivePreventiveAction($capa, $updateData);

        $this->assertEquals('Original title', $updated->title);
        $this->assertEquals(Priority::LOW->value, $updated->priority);
        $this->assertEquals('New assignee', $updated->assignee);
    }

    public function test_delete_removes_corrective_preventive_action(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create();
        $id = $capa->id;

        $result = $this->repository->deleteCorrectivePreventiveAction($capa);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('corrective_preventive_actions', ['id' => $id]);
    }

    public function test_update_action_through_workflow(): void
    {
        // Create a new action
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => Status::NEW->value,
            'assignee' => null,
        ]);

        // Assign to someone and move to in progress
        $capa = $this->repository->updateCorrectivePreventiveAction($capa, [
            'assignee' => 'John Doe',
            'status' => Status::IN_PROGRESS->value,
        ]);
        $this->assertEquals(Status::IN_PROGRESS->value, $capa->status);

        // Mark as pending verification
        $capa = $this->repository->updateCorrectivePreventiveAction($capa, [
            'status' => Status::PENDING_VERIFICATION->value,
            'verification_result' => VerificationResult::PENDING->value,
        ]);
        $this->assertEquals(Status::PENDING_VERIFICATION->value, $capa->status);

        // Close with verified effective result
        $capa = $this->repository->updateCorrectivePreventiveAction($capa, [
            'status' => Status::CLOSED->value,
            'verification_result' => VerificationResult::VERIFIED_EFFECTIVE->value,
            'evidence_link' => 'https://example.com/proof',
            'effectiveness_review_date' => now(),
        ]);

        $this->assertEquals(Status::CLOSED->value, $capa->status);
        $this->assertEquals(VerificationResult::VERIFIED_EFFECTIVE->value, $capa->verification_result);
        $this->assertNotNull($capa->effectiveness_review_date);
    }
}
