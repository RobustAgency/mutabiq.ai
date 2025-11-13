<?php

namespace Tests\Feature\Repositories;

use App\Models\AiIncident;
use App\Models\AiModel;
use App\Models\CorrectivePreventiveAction;
use App\Repositories\CorrectivePreventiveActionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;

class CorrectivePreventiveActionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CorrectivePreventiveActionRepository $repository;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CorrectivePreventiveActionRepository();
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
        $incident = AiIncident::factory()->create();
        $model = AiModel::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'source_type' => 'incident',
            'source_id' => (string) $incident->id,
            'model_id' => (string) $model->id,
            'title' => 'Test CAPA',
            'capa_type' => 'corrective',
            'priority' => 'high',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $capa = $this->repository->createCorrectivePreventiveAction($data);

        $this->assertInstanceOf(CorrectivePreventiveAction::class, $capa);
        $this->assertEquals('Test CAPA', $capa->title);
        $this->assertEquals('corrective', $capa->capa_type);
        $this->assertEquals('high', $capa->priority);

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'title' => 'Test CAPA',
            'capa_type' => 'corrective',
        ]);
    }

    public function test_create_stores_corrective_preventive_action_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $model = AiModel::factory()->create();
        $dueDate = now()->addDays(14);

        $data = [
            'organization_id' => $this->organization->id,
            'source_type' => 'incident',
            'source_id' => (string) $incident->id,
            'model_id' => (string) $model->id,
            'title' => 'Comprehensive CAPA',
            'capa_type' => 'both',
            'priority' => 'critical',
            'owner_team' => 'data_science',
            'assignee' => 'Jane Doe',
            'root_cause' => 'Insufficient model validation',
            'actions' => 'Implement automated testing, Add monitoring, Update documentation',
            'due_date' => $dueDate->format('Y-m-d'),
            'status' => 'in_progress',
            'verification_result' => null,
            'evidence_link' => null,
            'closed_at' => null,
        ];

        $capa = $this->repository->createCorrectivePreventiveAction($data);

        $this->assertEquals('Comprehensive CAPA', $capa->title);
        $this->assertEquals('both', $capa->capa_type);
        $this->assertEquals('Jane Doe', $capa->assignee);
        $this->assertEquals('Insufficient model validation', $capa->root_cause);
        $this->assertEquals('in_progress', $capa->status);
    }

    public function test_create_handles_corrective_capa_type(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'source_type' => 'incident',
            'source_id' => (string) $incident->id,
            'title' => 'Corrective action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $capa = $this->repository->createCorrectivePreventiveAction($data);

        $this->assertEquals('corrective', $capa->capa_type);
    }

    public function test_create_handles_preventive_capa_type(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'source_type' => 'incident',
            'source_id' => (string) $incident->id,
            'title' => 'Preventive action',
            'capa_type' => 'preventive',
            'priority' => 'low',
            'owner_team' => 'security',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'new',
        ];

        $capa = $this->repository->createCorrectivePreventiveAction($data);

        $this->assertEquals('preventive', $capa->capa_type);
    }

    public function test_create_handles_all_source_types(): void
    {
        $sourceTypes = ['incident', 'risk', 'feedback', 'override', 'audit', 'assessment', 'other'];

        foreach ($sourceTypes as $sourceType) {
            $data = [
                'organization_id' => $this->organization->id,
                'source_type' => $sourceType,
                'source_id' => '123',
                'title' => "Action from {$sourceType}",
                'capa_type' => 'corrective',
                'priority' => 'medium',
                'owner_team' => 'engineering',
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'new',
            ];

            $capa = $this->repository->createCorrectivePreventiveAction($data);
            $this->assertEquals($sourceType, $capa->source_type);
        }
    }

    public function test_create_handles_all_priorities(): void
    {
        $priorities = ['low', 'medium', 'high', 'critical'];

        foreach ($priorities as $priority) {
            $data = [
                'organization_id' => $this->organization->id,
                'source_type' => 'incident',
                'source_id' => '123',
                'title' => "Action with {$priority} priority",
                'capa_type' => 'corrective',
                'priority' => $priority,
                'owner_team' => 'engineering',
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'new',
            ];

            $capa = $this->repository->createCorrectivePreventiveAction($data);
            $this->assertEquals($priority, $capa->priority);
        }
    }

    public function test_create_handles_all_owner_teams(): void
    {
        $teams = ['product_ops', 'engineering', 'data_science', 'security', 'privacy', 'risk', 'legal', 'vendor_mgmt'];

        foreach ($teams as $team) {
            $data = [
                'organization_id' => $this->organization->id,
                'source_type' => 'incident',
                'source_id' => '123',
                'title' => "Action for {$team}",
                'capa_type' => 'corrective',
                'priority' => 'medium',
                'owner_team' => $team,
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'new',
            ];

            $capa = $this->repository->createCorrectivePreventiveAction($data);
            $this->assertEquals($team, $capa->owner_team);
        }
    }

    public function test_create_handles_all_statuses(): void
    {
        $statuses = ['new', 'in_progress', 'blocked', 'pending_verification', 'closed'];

        foreach ($statuses as $status) {
            $data = [
                'organization_id' => $this->organization->id,
                'source_type' => 'incident',
                'source_id' => '123',
                'title' => "Action with {$status} status",
                'capa_type' => 'corrective',
                'priority' => 'medium',
                'owner_team' => 'engineering',
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => $status,
            ];

            if ($status === 'closed') {
                $data['verification_result'] = 'passed';
            }

            $capa = $this->repository->createCorrectivePreventiveAction($data);
            $this->assertEquals($status, $capa->status);
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
            'status' => 'new',
            'priority' => 'medium',
            'assignee' => null,
        ]);

        $updateData = [
            'status' => 'in_progress',
            'priority' => 'high',
            'assignee' => 'John Smith',
        ];

        $updated = $this->repository->updateCorrectivePreventiveAction($capa, $updateData);

        $this->assertEquals('in_progress', $updated->status);
        $this->assertEquals('high', $updated->priority);
        $this->assertEquals('John Smith', $updated->assignee);
    }

    public function test_update_can_close_action(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => 'pending_verification',
            'verification_result' => 'pending',
        ]);

        $closedAt = now();
        $updateData = [
            'status' => 'closed',
            'verification_result' => 'passed',
            'evidence_link' => 'https://example.com/evidence',
            'closed_at' => $closedAt->toDateTimeString(),
        ];

        $updated = $this->repository->updateCorrectivePreventiveAction($capa, $updateData);

        $this->assertEquals('closed', $updated->status);
        $this->assertEquals('passed', $updated->verification_result);
        $this->assertEquals('https://example.com/evidence', $updated->evidence_link);
        $this->assertNotNull($updated->closed_at);
    }

    public function test_update_modifies_only_provided_fields(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'title' => 'Original title',
            'priority' => 'low',
            'assignee' => 'Original assignee',
        ]);

        $updateData = [
            'assignee' => 'New assignee',
        ];

        $updated = $this->repository->updateCorrectivePreventiveAction($capa, $updateData);

        $this->assertEquals('Original title', $updated->title);
        $this->assertEquals('low', $updated->priority);
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

    public function test_paginate_loads_ai_model_relationship(): void
    {
        CorrectivePreventiveAction::factory()->count(3)->create();

        $result = $this->repository->getFilteredCorrectivePreventiveActions(['per_page' => 1]);

        $capa = $result->items()[0];
        $this->assertTrue($capa->relationLoaded('aiModel'));
        if ($capa->model_id) {
            $this->assertInstanceOf(AiModel::class, $capa->aiModel);
        }
    }

    public function test_find_by_id_loads_ai_model_relationship(): void
    {
        $created = CorrectivePreventiveAction::factory()->create();

        $capa = $this->repository->getCorrectivePreventiveActionById($created);

        $this->assertTrue($capa->relationLoaded('aiModel'));
        if ($capa->model_id) {
            $this->assertInstanceOf(AiModel::class, $capa->aiModel);
        }
    }

    public function test_update_action_through_workflow(): void
    {
        // Create a new action
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => 'new',
            'assignee' => null,
        ]);

        // Assign to someone
        $capa = $this->repository->updateCorrectivePreventiveAction($capa, [
            'assignee' => 'John Doe',
            'status' => 'in_progress',
        ]);
        $this->assertEquals('in_progress', $capa->status);

        // Mark as pending verification
        $capa = $this->repository->updateCorrectivePreventiveAction($capa, [
            'status' => 'pending_verification',
            'verification_result' => 'pending',
        ]);
        $this->assertEquals('pending_verification', $capa->status);

        // Close with passed verification
        $capa = $this->repository->updateCorrectivePreventiveAction($capa, [
            'status' => 'closed',
            'verification_result' => 'passed',
            'evidence_link' => 'https://example.com/proof',
            'closed_at' => now()->toDateTimeString(),
        ]);

        $this->assertEquals('closed', $capa->status);
        $this->assertEquals('passed', $capa->verification_result);
        $this->assertNotNull($capa->closed_at);
    }
}
