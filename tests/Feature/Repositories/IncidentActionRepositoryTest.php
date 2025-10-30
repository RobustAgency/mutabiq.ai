<?php

namespace Tests\Feature\Repositories;

use App\Models\AiIncident;
use App\Models\IncidentAction;
use App\Repositories\IncidentActionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentActionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected IncidentActionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(IncidentActionRepository::class);
    }

    public function test_get_paginated_incident_actions_returns_paginated_results(): void
    {
        IncidentAction::factory()->count(25)->create();

        $result = $this->repository->getPaginatedIncidentActions(10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_create_incident_action_creates_new_record(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => 'kill_switch',
            'description' => 'Emergency kill switch activated',
            'performed_by' => 'John Doe',
            'started_at' => now()->subHour(),
            'validation_result' => 'passed',
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertInstanceOf(IncidentAction::class, $result);
        $this->assertEquals($incident->id, $result->ai_incident_id);
        $this->assertEquals('kill_switch', $result->action_type);
        $this->assertEquals('Emergency kill switch activated', $result->description);
        $this->assertEquals('John Doe', $result->performed_by);
        $this->assertEquals('passed', $result->validation_result);
        $this->assertDatabaseHas('incident_actions', [
            'id' => $result->id,
            'ai_incident_id' => $incident->id,
            'action_type' => 'kill_switch',
        ]);
    }

    public function test_create_incident_action_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => 'rollback_release',
            'description' => 'Rolled back to previous stable version',
            'performed_by' => 'Jane Smith',
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
            'validation_result' => 'passed',
            'validation_notes' => 'All systems operational after rollback',
            'linked_release_id' => 'REL-1234',
            'evidence_link' => 'https://example.com/evidence/rollback-123',
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertEquals('rollback_release', $result->action_type);
        $this->assertEquals('Rolled back to previous stable version', $result->description);
        $this->assertEquals('Jane Smith', $result->performed_by);
        $this->assertEquals('passed', $result->validation_result);
        $this->assertEquals('All systems operational after rollback', $result->validation_notes);
        $this->assertEquals('REL-1234', $result->linked_release_id);
        $this->assertEquals('https://example.com/evidence/rollback-123', $result->evidence_link);
        $this->assertNotNull($result->started_at);
        $this->assertNotNull($result->completed_at);
    }

    public function test_update_incident_action_updates_existing_record(): void
    {
        $action = IncidentAction::factory()->create([
            'action_type' => 'kill_switch',
            'description' => 'Original description',
            'validation_result' => 'pending',
        ]);

        $result = $this->repository->updateIncidentAction($action, [
            'action_type' => 'rollback_release',
            'description' => 'Updated description',
            'validation_result' => 'passed',
        ]);

        $this->assertEquals('rollback_release', $result->action_type);
        $this->assertEquals('Updated description', $result->description);
        $this->assertEquals('passed', $result->validation_result);
        $this->assertDatabaseHas('incident_actions', [
            'id' => $action->id,
            'action_type' => 'rollback_release',
            'description' => 'Updated description',
            'validation_result' => 'passed',
        ]);
    }

    public function test_update_incident_action_can_update_all_fields(): void
    {
        $action = IncidentAction::factory()->create();
        $newIncident = AiIncident::factory()->create();

        $updateData = [
            'ai_incident_id' => $newIncident->id,
            'action_type' => 'key_rotation',
            'description' => 'Rotated all API keys',
            'performed_by' => 'Security Team',
            'validation_result' => 'passed',
            'validation_notes' => 'All keys rotated successfully',
            'linked_release_id' => 'REL-9999',
            'evidence_link' => 'https://example.com/new-evidence',
        ];

        $result = $this->repository->updateIncidentAction($action, $updateData);

        $this->assertEquals($newIncident->id, $result->ai_incident_id);
        $this->assertEquals('key_rotation', $result->action_type);
        $this->assertEquals('Rotated all API keys', $result->description);
        $this->assertEquals('Security Team', $result->performed_by);
        $this->assertEquals('passed', $result->validation_result);
        $this->assertEquals('All keys rotated successfully', $result->validation_notes);
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
        $startedAt = now()->subHours(3);
        $completedAt = now()->subHour();

        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => 'traffic_throttle',
            'description' => 'Throttled traffic to 50%',
            'performed_by' => 'Operations Team',
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'validation_result' => 'passed',
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
        $actionTypes = [
            'kill_switch',
            'rollback_release',
            'key_rotation',
            'blocklist_update',
            'traffic_throttle',
            'model_disable_tool',
            'policy_change',
            'communication',
            'data_purge',
            'other',
        ];

        foreach ($actionTypes as $actionType) {
            $data = [
                'ai_incident_id' => $incident->id,
                'action_type' => $actionType,
                'description' => "Test description for {$actionType}",
                'performed_by' => 'Test User',
                'started_at' => now(),
                'validation_result' => 'passed',
            ];

            $result = $this->repository->createIncidentAction($data);

            $this->assertEquals($actionType, $result->action_type);
        }
    }

    public function test_create_incident_action_with_all_validation_results(): void
    {
        $incident = AiIncident::factory()->create();
        $validationResults = ['passed', 'failed', 'pending', 'not_applicable'];

        foreach ($validationResults as $result) {
            $data = [
                'ai_incident_id' => $incident->id,
                'action_type' => 'kill_switch',
                'description' => "Test description for {$result}",
                'performed_by' => 'Test User',
                'started_at' => now(),
                'validation_result' => $result,
            ];

            $created = $this->repository->createIncidentAction($data);

            $this->assertEquals($result, $created->validation_result);
        }
    }

    public function test_paginated_actions_loads_ai_incident_relationship(): void
    {
        IncidentAction::factory()->count(3)->create();

        $result = $this->repository->getPaginatedIncidentActions();

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
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => 'communication',
            'description' => 'Sent notification to stakeholders',
            'performed_by' => 'Communication Team',
            'started_at' => now(),
            'validation_result' => 'not_applicable',
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertNull($result->completed_at);
        $this->assertNull($result->validation_notes);
        $this->assertNull($result->linked_release_id);
        $this->assertNull($result->evidence_link);
    }

    public function test_update_incident_action_can_set_completed_at(): void
    {
        $action = IncidentAction::factory()->create([
            'completed_at' => null,
            'validation_result' => 'pending',
        ]);

        $completedAt = now();
        $result = $this->repository->updateIncidentAction($action, [
            'completed_at' => $completedAt,
            'validation_result' => 'passed',
        ]);

        $this->assertNotNull($result->completed_at);
        $this->assertEquals($completedAt->format('Y-m-d H:i:s'), $result->completed_at->format('Y-m-d H:i:s'));
        $this->assertEquals('passed', $result->validation_result);
    }

    public function test_create_incident_action_with_long_description(): void
    {
        $incident = AiIncident::factory()->create();
        $longDescription = str_repeat('This is a very long description. ', 100);

        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => 'policy_change',
            'description' => $longDescription,
            'performed_by' => 'Policy Team',
            'started_at' => now(),
            'validation_result' => 'passed',
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertEquals($longDescription, $result->description);
    }

    public function test_create_incident_action_with_long_validation_notes(): void
    {
        $incident = AiIncident::factory()->create();
        $longNotes = str_repeat('These are detailed validation notes. ', 50);

        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => 'data_purge',
            'description' => 'Purged compromised data',
            'performed_by' => 'Data Team',
            'started_at' => now(),
            'validation_result' => 'passed',
            'validation_notes' => $longNotes,
        ];

        $result = $this->repository->createIncidentAction($data);

        $this->assertEquals($longNotes, $result->validation_notes);
    }
}
