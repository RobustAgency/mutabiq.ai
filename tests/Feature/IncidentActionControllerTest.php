<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiIncident;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\IncidentAction;
use App\Enums\IncidentAction\ActionType;
use App\Enums\IncidentAction\ExecutionStatus;
use App\Enums\IncidentAction\ApprovalRequired;
use App\Enums\IncidentAction\ValidationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IncidentActionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_index_returns_paginated_incident_actions(): void
    {
        IncidentAction::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/incident-actions');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident actions retrieved successfully',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'display_id',
                            'organization_id',
                            'ai_incident_id',
                            'action_type',
                            'execution_status',
                            'description',
                            'performed_by',
                            'individual_name',
                            'depends_on',
                            'approval_required',
                            'estimated_duration',
                            'actual_duration',
                            'started_at',
                            'completed_at',
                            'validation_result',
                            'validation_notes',
                            'linked_release_id',
                            'evidence_link',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_store_creates_incident_action_with_required_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Emergency kill switch activated to stop the model',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->subHour()->toDateTimeString(),
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Incident action created successfully',
            ])
            ->assertJsonPath('data.ai_incident_id', $incident->id)
            ->assertJsonPath('data.action_type', ActionType::KILL_SWITCH->value)
            ->assertJsonPath('data.execution_status', ExecutionStatus::COMPLETED->value)
            ->assertJsonPath('data.description', 'Emergency kill switch activated to stop the model')
            ->assertJsonPath('data.performed_by', $stakeholder->id)
            ->assertJsonPath('data.validation_result', ValidationResult::EFFECTIVE->value);

        $this->assertDatabaseHas('incident_actions', [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'performed_by' => $stakeholder->id,
        ]);
    }

    public function test_store_creates_incident_action_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $startedAt = now()->subHours(2);
        $completedAt = now()->subHour();

        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::MODEL_ROLLBACK->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Rolled back to previous stable version',
            'performed_by' => $stakeholder->id,
            'individual_name' => 'Jane Smith',
            'depends_on' => 'Prior approval action',
            'approval_required' => ApprovalRequired::MANAGER_APPROVAL->value,
            'estimated_duration' => '120 hours',
            'actual_duration' => '95 hours',
            'started_at' => $startedAt->toDateTimeString(),
            'completed_at' => $completedAt->toDateTimeString(),
            'validation_result' => ValidationResult::EFFECTIVE->value,
            'validation_notes' => 'All systems operational after rollback',
            'linked_release_id' => 'REL-1234',
            'evidence_link' => 'https://example.com/evidence/rollback-123',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.action_type', ActionType::MODEL_ROLLBACK->value)
            ->assertJsonPath('data.execution_status', ExecutionStatus::COMPLETED->value)
            ->assertJsonPath('data.description', 'Rolled back to previous stable version')
            ->assertJsonPath('data.performed_by', $stakeholder->id)
            ->assertJsonPath('data.individual_name', 'Jane Smith')
            ->assertJsonPath('data.depends_on', 'Prior approval action')
            ->assertJsonPath('data.approval_required', ApprovalRequired::MANAGER_APPROVAL->value)
            ->assertJsonPath('data.estimated_duration', '120 hours')
            ->assertJsonPath('data.actual_duration', '95 hours')
            ->assertJsonPath('data.validation_result', ValidationResult::EFFECTIVE->value)
            ->assertJsonPath('data.validation_notes', 'All systems operational after rollback')
            ->assertJsonPath('data.linked_release_id', 'REL-1234')
            ->assertJsonPath('data.evidence_link', 'https://example.com/evidence/rollback-123');

        $this->assertDatabaseHas('incident_actions', [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::MODEL_ROLLBACK->value,
            'linked_release_id' => 'REL-1234',
        ]);
    }

    public function test_store_validates_ai_incident_id_is_required(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->toDateTimeString(),
            'validation_result' => ValidationResult::PENDING->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_incident_id']);
    }

    public function test_store_validates_ai_incident_id_exists(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => 99999,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->toDateTimeString(),
            'validation_result' => ValidationResult::PENDING->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_incident_id']);
    }

    public function test_store_validates_action_type_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->toDateTimeString(),
            'validation_result' => ValidationResult::PENDING->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action_type']);
    }

    public function test_store_validates_action_type_enum(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => 'invalid_action',
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->toDateTimeString(),
            'validation_result' => ValidationResult::PENDING->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action_type']);
    }

    public function test_store_validates_description_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'performed_by' => $stakeholder->id,
            'started_at' => now()->toDateTimeString(),
            'validation_result' => ValidationResult::PENDING->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_store_validates_performed_by_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'started_at' => now()->toDateTimeString(),
            'validation_result' => ValidationResult::PENDING->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['performed_by']);
    }

    public function test_store_validates_started_at_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'performed_by' => $stakeholder->id,
            'validation_result' => ValidationResult::PENDING->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['started_at']);
    }

    public function test_store_validates_validation_result_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['validation_result']);
    }

    public function test_store_validates_validation_result_enum(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->toDateTimeString(),
            'validation_result' => 'invalid_result',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['validation_result']);
    }

    public function test_store_validates_completed_at_after_or_equal_started_at(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->toDateTimeString(),
            'completed_at' => now()->subHour()->toDateTimeString(),
            'validation_result' => ValidationResult::PENDING->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['completed_at']);
    }

    public function test_store_validates_evidence_link_is_url(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Test description',
            'performed_by' => $stakeholder->id,
            'started_at' => now()->toDateTimeString(),
            'validation_result' => ValidationResult::PENDING->value,
            'evidence_link' => 'not-a-valid-url',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['evidence_link']);
    }

    public function test_store_accepts_all_valid_action_types(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        foreach (ActionType::cases() as $actionType) {
            $data = [
                'ai_incident_id' => $incident->id,
                'action_type' => $actionType->value,
                'execution_status' => ExecutionStatus::PLANNED->value,
                'description' => "Test description for {$actionType->name}",
                'performed_by' => $stakeholder->id,
                'started_at' => now()->toDateTimeString(),
                'validation_result' => ValidationResult::PENDING->value,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/incident-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.action_type', $actionType->value);
        }
    }

    public function test_store_accepts_all_valid_validation_results(): void
    {
        $incident = AiIncident::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        foreach (ValidationResult::cases() as $validationResult) {
            $data = [
                'ai_incident_id' => $incident->id,
                'action_type' => ActionType::KILL_SWITCH->value,
                'execution_status' => ExecutionStatus::COMPLETED->value,
                'description' => "Test description for {$validationResult->name}",
                'performed_by' => $stakeholder->id,
                'started_at' => now()->toDateTimeString(),
                'validation_result' => $validationResult->value,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/incident-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.validation_result', $validationResult->value);
        }
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/incident-actions', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_incident_action(): void
    {
        $action = IncidentAction::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/incident-actions/{$action->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident action retrieved successfully',
            ])
            ->assertJsonPath('data.id', $action->id)
            ->assertJsonPath('data.action_type', $action->action_type);
    }

    public function test_show_returns_404_for_non_existent_action(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/incident-actions/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $action = IncidentAction::factory()->create();

        $response = $this->getJson("/api/incident-actions/{$action->id}");

        $response->assertStatus(401);
    }

    public function test_update_updates_incident_action(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $action = IncidentAction::factory()->create([
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::PLANNED->value,
            'description' => 'Original description',
            'validation_result' => ValidationResult::PENDING->value,
        ]);

        $updateData = [
            'action_type' => ActionType::MODEL_ROLLBACK->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'description' => 'Updated description',
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-actions/{$action->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident action updated successfully',
            ])
            ->assertJsonPath('data.action_type', ActionType::MODEL_ROLLBACK->value)
            ->assertJsonPath('data.execution_status', ExecutionStatus::COMPLETED->value)
            ->assertJsonPath('data.description', 'Updated description')
            ->assertJsonPath('data.validation_result', ValidationResult::EFFECTIVE->value);

        $this->assertDatabaseHas('incident_actions', [
            'id' => $action->id,
            'action_type' => ActionType::MODEL_ROLLBACK->value,
            'description' => 'Updated description',
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ]);
    }

    public function test_update_partially_updates_incident_action(): void
    {
        $action = IncidentAction::factory()->create([
            'action_type' => ActionType::KILL_SWITCH->value,
            'validation_result' => ValidationResult::PENDING->value,
        ]);

        $updateData = [
            'validation_result' => ValidationResult::EFFECTIVE->value,
            'validation_notes' => 'Validation completed successfully',
            'execution_status' => ExecutionStatus::COMPLETED->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-actions/{$action->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.action_type', ActionType::KILL_SWITCH->value)
            ->assertJsonPath('data.validation_result', ValidationResult::EFFECTIVE->value)
            ->assertJsonPath('data.validation_notes', 'Validation completed successfully')
            ->assertJsonPath('data.execution_status', ExecutionStatus::COMPLETED->value);

        $this->assertDatabaseHas('incident_actions', [
            'id' => $action->id,
            'action_type' => ActionType::KILL_SWITCH->value,
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ]);
    }

    public function test_update_requires_authentication(): void
    {
        $action = IncidentAction::factory()->create();

        $response = $this->postJson("/api/incident-actions/{$action->id}", []);

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_incident_action(): void
    {
        $action = IncidentAction::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/incident-actions/{$action->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident action deleted successfully',
            ]);

        $this->assertDatabaseMissing('incident_actions', [
            'id' => $action->id,
        ]);
    }

    public function test_destroy_returns_404_for_non_existent_action(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/incident-actions/99999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $action = IncidentAction::factory()->create();

        $response = $this->deleteJson("/api/incident-actions/{$action->id}");

        $response->assertStatus(401);
    }
}
