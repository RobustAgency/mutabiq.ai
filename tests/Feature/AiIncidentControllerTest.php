<?php

namespace Tests\Feature;

use App\Models\AiIncident;
use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\User;
use App\Models\UseCase;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiIncidentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_index_returns_paginated_ai_incidents(): void
    {
        AiIncident::factory()->count(15)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-incidents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'summary',
                            'category',
                            'severity',
                            'status',
                            'stage',
                            'ic_owner',
                            'ai_model_id',
                            'ai_model_version_id',
                            'use_case_id',
                            'first_seen_at',
                            'declared_at',
                            'resolved_at',
                            'closed_at',
                            'impacted_users',
                            'impacted_data',
                            'impacted_systems',
                            'linked_release_id',
                            'linked_risk_id',
                            'linked_assessment_id',
                            'linked_capa_id',
                            'evidence_link',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ]
            ]);
    }

    public function test_index_returns_default_pagination(): void
    {
        AiIncident::factory()->count(20)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-incidents');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 15);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/ai-incidents');

        $response->assertStatus(401);
    }

    public function test_store_creates_ai_incident_with_all_fields(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create();
        $useCase = UseCase::factory()->create();

        $data = [
            'title' => 'Test Safety Incident',
            'summary' => 'This is a detailed summary of the safety incident.',
            'category' => 'safety',
            'severity' => 'sev1_critical',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'use_case_id' => $useCase->id,
            'first_seen_at' => now()->subHours(2)->toDateTimeString(),
            'declared_at' => now()->subHour()->toDateTimeString(),
            'impacted_users' => '1000+ users',
            'impacted_data' => ['pii', 'financial'],
            'impacted_systems' => 'Payment system, User database',
            'linked_release_id' => 'REL-123',
            'linked_risk_id' => 'RISK-456',
            'evidence_link' => 'https://evidence.example.com/incident-123',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'AI incident created successfully',
            ])
            ->assertJsonPath('data.title', 'Test Safety Incident')
            ->assertJsonPath('data.category', 'safety')
            ->assertJsonPath('data.severity', 'sev1_critical')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.stage', 'prod')
            ->assertJsonPath('data.ic_owner', 'John Doe')
            ->assertJsonPath('data.ai_model_id', $aiModel->id)
            ->assertJsonPath('data.ai_model_version_id', $aiModelVersion->id)
            ->assertJsonPath('data.use_case_id', $useCase->id)
            ->assertJsonPath('data.impacted_users', '1000+ users')
            ->assertJsonPath('data.linked_release_id', 'REL-123')
            ->assertJsonPath('data.evidence_link', 'https://evidence.example.com/incident-123');

        $this->assertDatabaseHas('ai_incidents', [
            'title' => 'Test Safety Incident',
            'category' => 'safety',
            'severity' => 'sev1_critical',
            'ai_model_id' => $aiModel->id,
        ]);
    }

    public function test_store_validates_title_is_required(): void
    {
        $data = [
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_validates_summary_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['summary']);
    }

    public function test_store_validates_first_seen_at_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_seen_at']);
    }

    public function test_store_validates_declared_at_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['declared_at']);
    }

    public function test_store_validates_impacted_data_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['impacted_data']);
    }

    public function test_store_validates_impacted_data_requires_at_least_one(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => [],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['impacted_data']);
    }

    public function test_store_validates_impacted_data_values(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['invalid_value'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['impacted_data.0']);
    }

    public function test_store_validates_category_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_store_validates_category_enum(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'invalid_category',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_store_validates_severity_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['severity']);
    }

    public function test_store_validates_severity_enum(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'invalid_severity',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['severity']);
    }

    public function test_store_validates_status_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_validates_status_enum(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'invalid_status',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_validates_stage_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stage']);
    }

    public function test_store_validates_stage_enum(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'invalid_stage',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stage']);
    }

    public function test_store_validates_ic_owner_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'category' => 'safety',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
            'declared_at' => now()->toDateTimeString(),
            'impacted_data' => ['pii'],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ic_owner']);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/ai-incidents', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_ai_incident(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'title' => 'Test Incident',
            'category' => 'privacy',
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/ai-incidents/{$aiIncident->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI incident retrieved successfully',
            ])
            ->assertJsonPath('data.id', $aiIncident->id)
            ->assertJsonPath('data.title', 'Test Incident')
            ->assertJsonPath('data.category', 'privacy');
    }

    public function test_show_returns_404_for_non_existent_ai_incident(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-incidents/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $response = $this->getJson("/api/ai-incidents/{$aiIncident->id}");

        $response->assertStatus(401);
    }

    public function test_update_modifies_ai_incident(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'title' => 'Original Title',
            'status' => 'open',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => 'resolved',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI incident updated successfully',
            ])
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.status', 'resolved');

        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'title' => 'Updated Title',
            'status' => 'resolved',
        ]);
    }

    public function test_update_supports_partial_updates(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'title' => 'Test Incident',
            'category' => 'security',
            'severity' => 'sev3_medium',
        ]);

        $updateData = ['severity' => 'sev1_critical'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Test Incident')
            ->assertJsonPath('data.category', 'security')
            ->assertJsonPath('data.severity', 'sev1_critical');
    }

    public function test_update_can_change_status(): void
    {
        $aiIncident = AiIncident::factory()->create(['status' => 'open']);

        $updateData = ['status' => 'closed'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'closed');

        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'status' => 'closed',
        ]);
    }

    public function test_update_can_change_ic_owner(): void
    {
        $aiIncident = AiIncident::factory()->create(['ic_owner' => 'John Doe']);

        $updateData = ['ic_owner' => 'Jane Smith'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.ic_owner', 'Jane Smith');

        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'ic_owner' => 'Jane Smith',
        ]);
    }

    public function test_update_validates_category_enum(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $updateData = ['category' => 'invalid_category'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_update_validates_severity_enum(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $updateData = ['severity' => 'invalid_severity'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['severity']);
    }

    public function test_update_validates_status_enum(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $updateData = ['status' => 'invalid_status'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_validates_stage_enum(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $updateData = ['stage' => 'invalid_stage'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stage']);
    }

    public function test_update_requires_authentication(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $response = $this->postJson("/api/ai-incidents/{$aiIncident->id}", []);

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_ai_incident(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/ai-incidents/{$aiIncident->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI incident deleted successfully',
            ]);

        $this->assertDatabaseMissing('ai_incidents', [
            'id' => $aiIncident->id,
        ]);
    }

    public function test_destroy_returns_404_for_non_existent_ai_incident(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/ai-incidents/99999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $response = $this->deleteJson("/api/ai-incidents/{$aiIncident->id}");

        $response->assertStatus(401);
    }

    public function test_store_accepts_all_valid_categories(): void
    {
        $categories = ['safety', 'privacy', 'security', 'bias_fairness', 'reliability', 'availability', 'legal_compliance', 'vendor', 'other'];

        foreach ($categories as $category) {
            $data = [
                'title' => "Test {$category} Incident",
                'summary' => 'Test summary',
                'category' => $category,
                'severity' => 'sev2_high',
                'status' => 'open',
                'stage' => 'prod',
                'ic_owner' => 'Test Owner',
                'first_seen_at' => now()->subHour()->toDateTimeString(),
                'declared_at' => now()->toDateTimeString(),
                'impacted_data' => ['pii'],
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-incidents', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.category', $category);
        }
    }

    public function test_store_accepts_all_valid_severities(): void
    {
        $severities = ['sev1_critical', 'sev2_high', 'sev3_medium', 'sev4_low', 'near_miss'];

        foreach ($severities as $severity) {
            $data = [
                'title' => "Test {$severity} Incident",
                'summary' => 'Test summary',
                'category' => 'safety',
                'severity' => $severity,
                'status' => 'open',
                'stage' => 'prod',
                'ic_owner' => 'Test Owner',
                'first_seen_at' => now()->subHour()->toDateTimeString(),
                'declared_at' => now()->toDateTimeString(),
                'impacted_data' => ['pii'],
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-incidents', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.severity', $severity);
        }
    }

    public function test_store_accepts_all_valid_statuses(): void
    {
        $statuses = ['open', 'contained', 'monitoring', 'resolved', 'closed'];

        foreach ($statuses as $status) {
            $data = [
                'title' => "Test {$status} Incident",
                'summary' => 'Test summary',
                'category' => 'safety',
                'severity' => 'sev2_high',
                'status' => $status,
                'stage' => 'prod',
                'ic_owner' => 'Test Owner',
                'first_seen_at' => now()->subHour()->toDateTimeString(),
                'declared_at' => now()->toDateTimeString(),
                'impacted_data' => ['pii'],
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-incidents', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.status', $status);
        }
    }

    public function test_store_accepts_all_valid_stages(): void
    {
        $stages = ['ideation', 'conception', 'dev', 'test', 'staging', 'prod', 'retirement'];

        foreach ($stages as $stage) {
            $data = [
                'title' => "Test {$stage} Incident",
                'summary' => 'Test summary',
                'category' => 'safety',
                'severity' => 'sev2_high',
                'status' => 'open',
                'stage' => $stage,
                'ic_owner' => 'Test Owner',
                'first_seen_at' => now()->subHour()->toDateTimeString(),
                'declared_at' => now()->toDateTimeString(),
                'impacted_data' => ['pii'],
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-incidents', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.stage', $stage);
        }
    }
}
