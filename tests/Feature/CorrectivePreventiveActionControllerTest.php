<?php

namespace Tests\Feature;

use App\Models\AiIncident;
use App\Models\AiModel;
use App\Models\CorrectivePreventiveAction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
                            'source_type',
                            'source_id',
                            'ai_model_id',
                            'title',
                            'capa_type',
                            'priority',
                            'owner_team',
                            'assignee',
                            'root_cause',
                            'actions',
                            'due_date',
                            'status',
                            'verification_result',
                            'evidence_link',
                            'closed_at',
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
        $incident = AiIncident::factory()->create();
        $model = AiModel::factory()->create();

        $data = [
            'source_type' => 'incident',
            'source_id' => (string) $incident->id,
            'model_id' => (string) $model->id,
            'title' => 'Fix data validation issue',
            'capa_type' => 'corrective',
            'priority' => 'high',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Corrective preventive action created successfully',
            ])
            ->assertJsonPath('data.title', 'Fix data validation issue')
            ->assertJsonPath('data.capa_type', 'corrective')
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.status', 'new');

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'title' => 'Fix data validation issue',
            'capa_type' => 'corrective',
            'priority' => 'high',
        ]);
    }

    public function test_store_creates_corrective_preventive_action_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $model = AiModel::factory()->create();

        $data = [
            'source_type' => 'incident',
            'source_id' => (string) $incident->id,
            'model_id' => (string) $model->id,
            'title' => 'Implement model monitoring',
            'capa_type' => 'preventive',
            'priority' => 'critical',
            'owner_team' => 'data_science',
            'assignee' => 'John Doe',
            'root_cause' => 'Lack of real-time monitoring led to delayed incident detection',
            'actions' => 'Set up monitoring dashboard, Configure alerts, Train team on new tools',
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'status' => 'in_progress',
            'verification_result' => null,
            'evidence_link' => null,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Implement model monitoring')
            ->assertJsonPath('data.capa_type', 'preventive')
            ->assertJsonPath('data.assignee', 'John Doe')
            ->assertJsonPath('data.root_cause', 'Lack of real-time monitoring led to delayed incident detection');

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'title' => 'Implement model monitoring',
            'assignee' => 'John Doe',
        ]);
    }

    public function test_store_validates_source_type_is_required(): void
    {
        $data = [
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
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
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_type']);
    }

    public function test_store_validates_source_id_is_required(): void
    {
        $data = [
            'source_type' => 'incident',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_id']);
    }

    public function test_store_validates_title_is_required(): void
    {
        $data = [
            'source_type' => 'incident',
            'source_id' => '123',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_validates_capa_type_is_required(): void
    {
        $data = [
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['capa_type']);
    }

    public function test_store_validates_capa_type_enum(): void
    {
        $data = [
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'invalid_type',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['capa_type']);
    }

    public function test_store_validates_priority_is_required(): void
    {
        $data = [
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_store_validates_priority_enum(): void
    {
        $data = [
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'invalid_priority',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_store_validates_owner_team_is_required(): void
    {
        $data = [
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['owner_team']);
    }

    public function test_store_validates_owner_team_enum(): void
    {
        $data = [
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'invalid_team',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['owner_team']);
    }

    public function test_store_validates_due_date_is_required(): void
    {
        $data = [
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'status' => 'new',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_store_validates_status_is_required(): void
    {
        $data = [
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
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
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
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
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'new',
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
            'source_type' => 'incident',
            'source_id' => '123',
            'title' => 'Test action',
            'capa_type' => 'corrective',
            'priority' => 'medium',
            'owner_team' => 'engineering',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'closed',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/corrective-preventive-actions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['verification_result']);
    }

    public function test_store_accepts_all_valid_source_types(): void
    {
        $sourceTypes = ['incident', 'risk', 'feedback', 'override', 'audit', 'assessment', 'other'];

        foreach ($sourceTypes as $sourceType) {
            $data = [
                'source_type' => $sourceType,
                'source_id' => '123',
                'title' => "Test action for {$sourceType}",
                'capa_type' => 'corrective',
                'priority' => 'medium',
                'owner_team' => 'engineering',
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'new',
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.source_type', $sourceType);
        }
    }

    public function test_store_accepts_all_valid_capa_types(): void
    {
        $capaTypes = ['corrective', 'preventive', 'both'];

        foreach ($capaTypes as $capaType) {
            $data = [
                'source_type' => 'incident',
                'source_id' => '123',
                'title' => "Test action for {$capaType}",
                'capa_type' => $capaType,
                'priority' => 'medium',
                'owner_team' => 'engineering',
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'new',
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.capa_type', $capaType);
        }
    }

    public function test_store_accepts_all_valid_priorities(): void
    {
        $priorities = ['low', 'medium', 'high', 'critical'];

        foreach ($priorities as $priority) {
            $data = [
                'source_type' => 'incident',
                'source_id' => '123',
                'title' => "Test action with {$priority} priority",
                'capa_type' => 'corrective',
                'priority' => $priority,
                'owner_team' => 'engineering',
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'new',
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.priority', $priority);
        }
    }

    public function test_store_accepts_all_valid_owner_teams(): void
    {
        $teams = ['product_ops', 'engineering', 'data_science', 'security', 'privacy', 'risk', 'legal', 'vendor_mgmt'];

        foreach ($teams as $team) {
            $data = [
                'source_type' => 'incident',
                'source_id' => '123',
                'title' => "Test action for {$team}",
                'capa_type' => 'corrective',
                'priority' => 'medium',
                'owner_team' => $team,
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'new',
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.owner_team', $team);
        }
    }

    public function test_store_accepts_all_valid_statuses(): void
    {
        $statuses = [
            'new' => false,
            'in_progress' => false,
            'blocked' => false,
            'pending_verification' => false,
            'closed' => true,
        ];

        foreach ($statuses as $status => $requiresVerification) {
            $data = [
                'source_type' => 'incident',
                'source_id' => '123',
                'title' => "Test action with {$status} status",
                'capa_type' => 'corrective',
                'priority' => 'medium',
                'owner_team' => 'engineering',
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => $status,
            ];

            if ($requiresVerification) {
                $data['verification_result'] = 'passed';
            }

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/corrective-preventive-actions', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.status', $status);
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
            'status' => 'new',
            'priority' => 'medium',
        ]);

        $updateData = [
            'status' => 'in_progress',
            'priority' => 'high',
            'assignee' => 'Jane Smith',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/corrective-preventive-actions/{$capa->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Corrective preventive action updated successfully',
            ])
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.assignee', 'Jane Smith');

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'id' => $capa->id,
            'status' => 'in_progress',
            'priority' => 'high',
        ]);
    }

    public function test_update_can_close_action_with_verification(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => 'pending_verification',
        ]);

        $updateData = [
            'status' => 'closed',
            'verification_result' => 'passed',
            'evidence_link' => 'https://example.com/evidence',
            'closed_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/corrective-preventive-actions/{$capa->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'closed')
            ->assertJsonPath('data.verification_result', 'passed');

        $this->assertDatabaseHas('corrective_preventive_actions', [
            'id' => $capa->id,
            'status' => 'closed',
            'verification_result' => 'passed',
        ]);
    }

    public function test_update_requires_verification_result_when_closing(): void
    {
        $capa = CorrectivePreventiveAction::factory()->create([
            'status' => 'in_progress',
        ]);

        $updateData = [
            'status' => 'closed',
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
