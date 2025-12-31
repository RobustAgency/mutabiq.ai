<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
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
use App\Enums\CorrectivePreventiveAction\VerificationResult;

class CorrectivePreventiveActionControllerTest extends TestCase
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

    public function test_index_returns_paginated_corrective_preventive_actions(): void
    {
        CorrectivePreventiveAction::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/corrective-preventive-actions');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Corrective preventive actions retrieved successfully',
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
                            'source_type',
                            'source_reference',
                            'ai_model_id',
                            'dataset_id',
                            'title',
                            'capa_type',
                            'priority',
                            'root_cause',
                            'actions',
                            'owner_team',
                            'assignee',
                            'due_date',
                            'status',
                            'success_criteria',
                            'linked_training',
                            'estimated_cost',
                            'effectiveness_review_date',
                            'verification_result',
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

    public function test_store_creates_corrective_preventive_action_with_required_fields(): void
    {
        $model = AiModel::factory()->create();

        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-12345',
            'ai_model_id' => $model->id,
            'title' => 'Fix data validation issue',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::HIGH->value,
            'actions' => 'Take immediate action',
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Corrective preventive action created successfully',
            ])
            ->assertJsonPath('data.title', 'Fix data validation issue')
            ->assertJsonPath('data.capa_type', CapaType::CORRECTIVE->value)
            ->assertJsonPath('data.priority', Priority::HIGH->value)
            ->assertJsonPath('data.status', Status::NEW->value);

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'title' => 'Fix data validation issue',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::HIGH->value,
        ]);
    }

    public function test_store_creates_corrective_preventive_action_with_all_fields(): void
    {
        $model = AiModel::factory()->create();
        $dataset = Dataset::factory()->create();
        $dueDate = now()->addDays(14);
        $reviewDate = now()->addDays(30);

        $data = [
            'source_type' => SourceType::RCA->value,
            'source_reference' => 'RCA-67890',
            'ai_model_id' => $model->id,
            'dataset_id' => $dataset->id,
            'title' => 'Implement model monitoring',
            'capa_type' => CapaType::PREVENTIVE->value,
            'priority' => Priority::CRITICAL->value,
            'root_cause' => 'Lack of real-time monitoring led to delayed incident detection',
            'actions' => 'Set up monitoring dashboard, Configure alerts, Train team on new tools',
            'owner_team' => OwnerTeam::DATA_GOVERNANCE->value,
            'assignee' => 'John Doe',
            'due_date' => $dueDate->format('Y-m-d'),
            'status' => Status::IN_PROGRESS->value,
            'success_criteria' => 'Model accuracy > 95%',
            'linked_training' => 'Model Validation Best Practices',
            'estimated_cost' => 5000.00,
            'effectiveness_review_date' => $reviewDate->format('Y-m-d'),
            'verification_result' => null,
            'evidence_link' => null,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Implement model monitoring')
            ->assertJsonPath('data.capa_type', CapaType::PREVENTIVE->value)
            ->assertJsonPath('data.assignee', 'John Doe')
            ->assertJsonPath('data.root_cause', 'Lack of real-time monitoring led to delayed incident detection')
            ->assertJsonPath('data.success_criteria', 'Model accuracy > 95%')
            ->assertJsonPath('data.linked_training', 'Model Validation Best Practices');

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'title' => 'Implement model monitoring',
            'assignee' => 'John Doe',
        ]);
    }

    public function test_store_validates_source_type_is_required(): void
    {
        $data = [
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_type']);
    }

    public function test_store_validates_source_type_enum(): void
    {
        $data = [
            'source_type' => 'invalid_source',
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_type']);
    }

    public function test_store_validates_source_reference_is_required(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_reference']);
    }

    public function test_store_validates_title_is_required(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_validates_capa_type_is_required(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['capa_type']);
    }

    public function test_store_validates_capa_type_enum(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => 'invalid_type',
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['capa_type']);
    }

    public function test_store_validates_priority_is_required(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_store_validates_priority_enum(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => 'invalid_priority',
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_store_validates_owner_team_is_required(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['owner_team']);
    }

    public function test_store_validates_owner_team_enum(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => 'invalid_team',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['owner_team']);
    }

    public function test_store_validates_due_date_is_required(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'status' => Status::NEW->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_store_validates_status_is_required(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_validates_status_enum(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_validates_evidence_link_is_url(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::NEW->value,
            'evidence_link' => 'not-a-valid-url',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['evidence_link']);
    }

    public function test_store_requires_verification_result_when_status_is_closed(): void
    {
        $data = [
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => 'INC-123',
            'title' => 'Test action',
            'capa_type' => CapaType::CORRECTIVE->value,
            'priority' => Priority::MEDIUM->value,
            'owner_team' => OwnerTeam::ML_ENGINEERING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Status::CLOSED->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['verification_result']);
    }

    public function test_store_accepts_all_valid_source_types(): void
    {
        foreach (SourceType::cases() as $sourceType) {
            $data = [
                'source_type' => $sourceType->value,
                'source_reference' => "REF-{$sourceType->name}",
                'title' => "Test action for {$sourceType->name}",
                'capa_type' => CapaType::CORRECTIVE->value,
                'priority' => Priority::MEDIUM->value,
                'actions' => 'Take action',
                'owner_team' => OwnerTeam::ML_ENGINEERING->value,
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => Status::NEW->value,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.source_type', $sourceType->value);
        }
    }

    public function test_store_accepts_all_valid_capa_types(): void
    {
        foreach (CapaType::cases() as $capaType) {
            $data = [
                'source_type' => SourceType::INCIDENT->value,
                'source_reference' => 'INC-123',
                'title' => "Test action for {$capaType->name}",
                'capa_type' => $capaType->value,
                'priority' => Priority::MEDIUM->value,
                'actions' => 'Take action',
                'owner_team' => OwnerTeam::ML_ENGINEERING->value,
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => Status::NEW->value,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.capa_type', $capaType->value);
        }
    }

    public function test_store_accepts_all_valid_priorities(): void
    {
        foreach (Priority::cases() as $priority) {
            $data = [
                'source_type' => SourceType::INCIDENT->value,
                'source_reference' => 'INC-123',
                'title' => "Test action with {$priority->name} priority",
                'capa_type' => CapaType::CORRECTIVE->value,
                'priority' => $priority->value,
                'actions' => 'Take action',
                'owner_team' => OwnerTeam::ML_ENGINEERING->value,
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => Status::NEW->value,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.priority', $priority->value);
        }
    }

    public function test_store_accepts_all_valid_owner_teams(): void
    {
        foreach (OwnerTeam::cases() as $team) {
            $data = [
                'source_type' => SourceType::INCIDENT->value,
                'source_reference' => 'INC-123',
                'title' => "Test action for {$team->name}",
                'capa_type' => CapaType::CORRECTIVE->value,
                'priority' => Priority::MEDIUM->value,
                'actions' => 'Take action',
                'owner_team' => $team->value,
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => Status::NEW->value,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.owner_team', $team->value);
        }
    }

    public function test_store_accepts_all_valid_statuses(): void
    {
        foreach (Status::cases() as $status) {
            $data = [
                'source_type' => SourceType::INCIDENT->value,
                'source_reference' => 'INC-123',
                'title' => "Test action with {$status->name} status",
                'capa_type' => CapaType::CORRECTIVE->value,
                'priority' => Priority::MEDIUM->value,
                'actions' => 'Take action',
                'owner_team' => OwnerTeam::ML_ENGINEERING->value,
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => $status->value,
            ];

            // Add verification result when status is closed
            if ($status === Status::CLOSED) {
                $data['verification_result'] = VerificationResult::VERIFIED_EFFECTIVE->value;
            }

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.status', $status->value);
        }
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/corrective-preventive-actions', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_corrective_preventive_action(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/corrective-preventive-actions/{$capa->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Corrective preventive action retrieved successfully',
            ])
            ->assertJsonPath('data.id', $capa->id)
            ->assertJsonPath('data.title', $capa->title);
    }

    public function test_show_returns_404_for_non_existent_action(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/corrective-preventive-actions/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create();

        $response = $this->getJson("/api/corrective-preventive-actions/{$capa->id}");

        $response->assertStatus(401);
    }

    public function test_update_updates_corrective_preventive_action(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => Status::NEW->value,
            'priority' => Priority::MEDIUM->value,
        ]);

        $updateData = [
            'status' => Status::IN_PROGRESS->value,
            'priority' => Priority::HIGH->value,
            'assignee' => 'Jane Smith',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/corrective-preventive-actions/{$capa->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Corrective preventive action updated successfully',
            ])
            ->assertJsonPath('data.status', Status::IN_PROGRESS->value)
            ->assertJsonPath('data.priority', Priority::HIGH->value)
            ->assertJsonPath('data.assignee', 'Jane Smith');

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'id' => $capa->id,
            'status' => Status::IN_PROGRESS->value,
            'priority' => Priority::HIGH->value,
        ]);
    }

    public function test_update_can_close_action_with_verification(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => Status::PENDING_VERIFICATION->value,
        ]);

        $updateData = [
            'status' => Status::CLOSED->value,
            'verification_result' => VerificationResult::VERIFIED_EFFECTIVE->value,
            'evidence_link' => 'https://example.com/evidence',
            'effectiveness_review_date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/corrective-preventive-actions/{$capa->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', Status::CLOSED->value)
            ->assertJsonPath('data.verification_result', VerificationResult::VERIFIED_EFFECTIVE->value);

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'id' => $capa->id,
            'status' => Status::CLOSED->value,
            'verification_result' => VerificationResult::VERIFIED_EFFECTIVE->value,
        ]);
    }

    public function test_update_requires_verification_result_when_closing(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => Status::IN_PROGRESS->value,
        ]);

        $updateData = [
            'status' => Status::CLOSED->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/corrective-preventive-actions/{$capa->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['verification_result']);
    }

    public function test_update_partially_updates_action(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'title' => 'Original title',
            'assignee' => null,
        ]);

        $updateData = [
            'assignee' => 'John Doe',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/corrective-preventive-actions/{$capa->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Original title')
            ->assertJsonPath('data.assignee', 'John Doe');
    }

    public function test_update_requires_authentication(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create();

        $response = $this->postJson("/api/corrective-preventive-actions/{$capa->id}", []);

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_corrective_preventive_action(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/corrective-preventive-actions/{$capa->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Corrective preventive action deleted successfully',
            ]);

        $this->assertDatabaseMissing('corrective_preventive_actions', [
            'id' => $capa->id,
        ]);
    }

    public function test_destroy_returns_404_for_non_existent_action(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/corrective-preventive-actions/99999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create();

        $response = $this->deleteJson("/api/corrective-preventive-actions/{$capa->id}");

        $response->assertStatus(401);
    }
}
