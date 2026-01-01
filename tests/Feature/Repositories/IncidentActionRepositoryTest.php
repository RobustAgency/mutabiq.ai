<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\AiIncident;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\IncidentAction;
use App\Enums\IncidentAction\ActionType;
use App\Enums\IncidentAction\ExecutionStatus;
use App\Enums\IncidentAction\ApprovalRequired;
use App\Enums\IncidentAction\ValidationResult;
use App\Repositories\IncidentActionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IncidentActionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected IncidentActionRepository $repository;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(IncidentActionRepository::class);
        $this->organization = Organization::factory()->create();
    }

    public function test_get_paginated_incident_actions_returns_paginated_results(): void
    {
        IncidentAction::factory()->count(25)->create();

        $result = $this->repository->getFilteredIncidentActions(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_create_incident_action_creates_new_record(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Emergency kill switch activated',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->subHour(),
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertInstanceOf(IncidentAction::class, $result);
        $this->assertEquals($incident->id, $result->ai_incident_id);
        $this->assertEquals(ActionType::KILL_SWITCH->value, $result->action_type);
        $this->assertEquals(ExecutionStatus::COMPLETED->value, $result->execution_status);
        $this->assertEquals('Emergency kill switch activated', $result->description);
        $this->assertEquals($stakeholder->id, $result->performed_by);
        $this->assertEquals(ValidationResult::EFFECTIVE->value, $result->validation_result);
        $this->assertDatabaseHas('incident_actions', [
            'id' => $result->id,
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
        ]);
    }

    public function test_create_incident_action_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::MODEL_ROLLBACK->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Rolled back to previous stable version',
            'performed_by' => $stakeholder->id,
            'individual_name' => 'Jane Smith',
            'depends_on' => 'Action 123',
            'approval_required' => ApprovalRequired::MANAGER_APPROVAL->value,
            'estimated_duration' => '120 hours',
            'actual_duration' => '90 hours',
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
            'validation_result' => ValidationResult::EFFECTIVE->value,
            'validation_notes' => 'All systems operational after rollback',
            'linked_release_id' => 'REL-1234',
            'evidence_link' => 'https://example.com/evidence/rollback-123',
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertEquals(ActionType::MODEL_ROLLBACK->value, $result->action_type);
        $this->assertEquals(ExecutionStatus::COMPLETED->value, $result->execution_status);
        $this->assertEquals('Rolled back to previous stable version', $result->description);
        $this->assertEquals($stakeholder->id, $result->performed_by);
        $this->assertEquals('Jane Smith', $result->individual_name);
        $this->assertEquals('Action 123', $result->depends_on);
        $this->assertEquals(ApprovalRequired::MANAGER_APPROVAL->value, $result->approval_required);
        $this->assertEquals('120 hours', $result->estimated_duration);
        $this->assertEquals('90 hours', $result->actual_duration);
        $this->assertEquals(ValidationResult::EFFECTIVE->value, $result->validation_result);
        $this->assertEquals('All systems operational after rollback', $result->validation_notes);
        $this->assertEquals('REL-1234', $result->linked_release_id);
        $this->assertEquals('https://example.com/evidence/rollback-123', $result->evidence_link);
        $this->assertNotNull($result->started_at);
        $this->assertNotNull($result->completed_at);
    }

    public function test_update_incident_action_updates_existing_record(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $action = IncidentAction::factory()->create([
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Original description',
            'validation_result' => ValidationResult::PENDING->value,
        ]);

        $result = $this->repository->updateIncidentAction($action, [
            'action_type' => ActionType::MODEL_ROLLBACK->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Updated description',
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ]);

        $this->assertEquals(ActionType::MODEL_ROLLBACK->value, $result->action_type);
        $this->assertEquals(ExecutionStatus::COMPLETED->value, $result->execution_status);
        $this->assertEquals('Updated description', $result->description);
        $this->assertEquals(ValidationResult::EFFECTIVE->value, $result->validation_result);
        $this->assertDatabaseHas('incident_actions', [
            'id' => $action->id,
            'action_type' => ActionType::MODEL_ROLLBACK->value,
            'description' => 'Updated description',
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ]);
    }

    public function test_update_incident_action_can_update_all_fields(): void
    {
        $action = IncidentAction::factory()->create();
        $newIncident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $updateData = [
            'ai_incident_id' => $newIncident->id,
            'action_type' => ActionType::DATA_ISOLATION->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Isolated compromised data',
            'performed_by' => $stakeholder->id,
            'individual_name' => 'Security Team',
            'depends_on' => 'Prior action completed',
            'approval_required' => ApprovalRequired::EXECUTIVE_APPROVAL->value,
            'estimated_duration' => 240,
            'actual_duration' => 200,
            'validation_result' => ValidationResult::EFFECTIVE->value,
            'validation_notes' => 'Data isolation completed successfully',
            'linked_release_id' => 'REL-9999',
            'evidence_link' => 'https://example.com/new-evidence',
        ];

        $result = $this->repository->updateIncidentAction($action, $updateData);

        $this->assertEquals($newIncident->id, $result->ai_incident_id);
        $this->assertEquals(ActionType::DATA_ISOLATION->value, $result->action_type);
        $this->assertEquals(ExecutionStatus::COMPLETED->value, $result->execution_status);
        $this->assertEquals('Isolated compromised data', $result->description);
        $this->assertEquals($stakeholder->id, $result->performed_by);
        $this->assertEquals('Security Team', $result->individual_name);
        $this->assertEquals('Prior action completed', $result->depends_on);
        $this->assertEquals(ApprovalRequired::EXECUTIVE_APPROVAL->value, $result->approval_required);
        $this->assertEquals(240, $result->estimated_duration);
        $this->assertEquals(200, $result->actual_duration);
        $this->assertEquals(ValidationResult::EFFECTIVE->value, $result->validation_result);
        $this->assertEquals('Data isolation completed successfully', $result->validation_notes);
        $this->assertEquals('REL-9999', $result->linked_release_id);
        $this->assertEquals('https://example.com/new-evidence', $result->evidence_link);
    }

    public function test_delete_incident_action_removes_record(): void
    {
        $action = IncidentAction::factory()->create();

        $result = $this->repository->deleteIncidentAction($action);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('incident_actions', ['id' => $action->id]);
    }

    public function test_get_incident_action_by_id_returns_action(): void
    {
        $action = IncidentAction::factory()->create();
        $result = $this->repository->getIncidentActionById($action);

        $this->assertInstanceOf(IncidentAction::class, $result);
        $this->assertEquals($action->id, $result->id);
    }

    public function test_create_incident_action_with_datetime_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $startedAt = now()->subHours(3);
        $completedAt = now()->subHour();

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::SYSTEM_PATCH->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Applied critical system patches',
            'performed_by' => $stakeholder->id,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertInstanceOf(\Carbon\Carbon::class, $result->started_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result->completed_at);
        $this->assertEquals($startedAt->format('Y-m-d H:i:s'), $result->started_at->format('Y-m-d H:i:s'));
        $this->assertEquals($completedAt->format('Y-m-d H:i:s'), $result->completed_at->format('Y-m-d H:i:s'));
    }

    public function test_create_incident_action_with_all_action_types(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        foreach (ActionType::cases() as $actionType) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'action_type' => $actionType->value,
                'execution_status' => ExecutionStatus::COMPLETED->value,
                'description' => "Test description for {$actionType->name}",
                'performed_by' => $stakeholder->id,
                'started_at' => now(),
                'validation_result' => ValidationResult::EFFECTIVE->value,
            ];

            $result = $this->repository->createIncidentAction($data);

            $this->assertEquals($actionType->value, $result->action_type);
        }
    }

    public function test_create_incident_action_with_all_validation_results(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        foreach (ValidationResult::cases() as $validationResult) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'action_type' => ActionType::KILL_SWITCH->value,
                'execution_status' => ExecutionStatus::COMPLETED->value,
                'description' => "Test description for {$validationResult->name}",
                'performed_by' => $stakeholder->id,
                'started_at' => now(),
                'validation_result' => $validationResult->value,
            ];

            $created = $this->repository->createIncidentAction($data);

            $this->assertEquals($validationResult->value, $created->validation_result);
        }
    }

    public function test_paginated_actions_loads_ai_incident_relationship(): void
    {
        IncidentAction::factory()->count(3)->create();

        $result = $this->repository->getFilteredIncidentActions();

        $this->assertTrue($result->items()[0]->relationLoaded('aiIncident'));
    }

    public function test_get_by_id_loads_ai_incident_relationship(): void
    {
        $action = IncidentAction::factory()->create();

        $result = $this->repository->getIncidentActionById($action);

        $this->assertTrue($result->relationLoaded('aiIncident'));
    }

    public function test_update_incident_action_returns_fresh_instance(): void
    {
        $action = IncidentAction::factory()->create([
            'description' => 'Original description',
        ]);

        $result = $this->repository->updateIncidentAction($action, [
            'description' => 'Updated description',
        ]);

        $this->assertEquals('Updated description', $result->description);
        $this->assertNotSame($action, $result);
    }

    public function test_create_incident_action_without_optional_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::COMMUNICATION_NOTIFICATION->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Sent notification to stakeholders',
            'performed_by' => $stakeholder->id,
            'started_at' => now(),
            'validation_result' => ValidationResult::PENDING->value,
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertNull($result->completed_at);
        $this->assertNull($result->individual_name);
        $this->assertNull($result->depends_on);
        $this->assertNull($result->approval_required);
        $this->assertNull($result->estimated_duration);
        $this->assertNull($result->actual_duration);
        $this->assertNull($result->validation_notes);
        $this->assertNull($result->linked_release_id);
        $this->assertNull($result->evidence_link);
    }

    public function test_update_incident_action_can_set_completed_at(): void
    {
        $action = IncidentAction::factory()->create([
            'completed_at' => null,
            'validation_result' => ValidationResult::PENDING->value,
        ]);

        $completedAt = now();
        $result = $this->repository->updateIncidentAction($action, [
            'completed_at' => $completedAt,
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ]);

        $this->assertNotNull($result->completed_at);
        $this->assertEquals($completedAt->format('Y-m-d H:i:s'), $result->completed_at->format('Y-m-d H:i:s'));
        $this->assertEquals(ValidationResult::EFFECTIVE->value, $result->validation_result);
    }

    public function test_create_incident_action_with_long_description(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $longDescription = str_repeat('This is a very long description. ', 100);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::CONFIGURATION_CHANGE->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => $longDescription,
            'performed_by' => $stakeholder->id,
            'started_at' => now(),
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertEquals($longDescription, $result->description);
    }

    public function test_create_incident_action_with_long_validation_notes(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $longNotes = str_repeat('These are detailed validation notes. ', 50);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::ERADICATION->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Eradicated malicious code',
            'performed_by' => $stakeholder->id,
            'started_at' => now(),
            'validation_result' => ValidationResult::EFFECTIVE->value,
            'validation_notes' => $longNotes,
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertEquals($longNotes, $result->validation_notes);
    }
}
