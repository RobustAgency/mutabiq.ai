<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiIncident;
use App\Models\Organization;
use App\Models\IncidentRootCauseAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IncidentRootCauseAnalysisControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
    }

    public function test_index_returns_paginated_incident_root_cause_analyses(): void
    {
        IncidentRootCauseAnalysis::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/incident-root-cause-analyses');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident root cause analyses retrieved successfully',
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
                            'rca_method',
                            'analysis_date',
                            'immediate_cause',
                            'root_causes',
                            'contributing_factors',
                            'control_failures',
                            'recommendations',
                            'lead_analyst',
                            'review_committee',
                            'approved_at',
                            'report_link',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_store_creates_incident_root_cause_analysis_with_required_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now()->subDays(3);
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'Model returned incorrect predictions due to data drift',
            'root_causes' => 'Lack of continuous monitoring and retraining processes',
            'recommendations' => 'Set up quarterly retraining schedule and implement monitoring alerts',
            'lead_analyst' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Incident root cause analysis created successfully',
            ])
            ->assertJsonPath('data.ai_incident_id', $incident->id)
            ->assertJsonPath('data.rca_method', 'five_whys')
            ->assertJsonPath('data.immediate_cause', 'Model returned incorrect predictions due to data drift')
            ->assertJsonPath('data.lead_analyst', 'John Doe');

        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
            'lead_analyst' => 'John Doe',
        ]);
    }

    public function test_store_creates_incident_root_cause_analysis_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $approvedAt = now()->subDays(2);
        $analysisDate = now()->subDays(5);

        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fishbone',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'API timeout caused model failures',
            'root_causes' => 'Insufficient capacity planning and lack of load testing',
            'contributing_factors' => 'Unexpected traffic spike during product launch',
            'control_failures' => 'Monitoring alerts not triggered, auto-scaling disabled',
            'recommendations' => 'Implement chaos engineering practices and improve monitoring',
            'lead_analyst' => 'Jane Smith',
            'review_committee' => 'John Doe | Sarah Johnson | Mike Chen',
            'approved_at' => $approvedAt->toDateTimeString(),
            'report_link' => 'https://example.com/rca/full-report-123',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.rca_method', 'fishbone')
            ->assertJsonPath('data.immediate_cause', 'API timeout caused model failures')
            ->assertJsonPath('data.contributing_factors', 'Unexpected traffic spike during product launch')
            ->assertJsonPath('data.control_failures', 'Monitoring alerts not triggered, auto-scaling disabled')
            ->assertJsonPath('data.lead_analyst', 'Jane Smith')
            ->assertJsonPath('data.review_committee', 'John Doe | Sarah Johnson | Mike Chen')
            ->assertJsonPath('data.report_link', 'https://example.com/rca/full-report-123');

        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fishbone',
            'lead_analyst' => 'Jane Smith',
        ]);
    }

    public function test_store_validates_ai_incident_id_is_required(): void
    {
        $analysisDate = now();
        $data = [
            'rca_method' => 'five_whys',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'Test cause',
            'root_causes' => 'Test root causes',
            'recommendations' => 'Test recommendations',
            'lead_analyst' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_incident_id']);
    }

    public function test_store_validates_ai_incident_id_exists(): void
    {
        $analysisDate = now();
        $data = [
            'ai_incident_id' => 99999,
            'rca_method' => 'five_whys',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'Test cause',
            'root_causes' => 'Test root causes',
            'recommendations' => 'Test recommendations',
            'lead_analyst' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_incident_id']);
    }

    public function test_store_validates_rca_method_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now();
        $data = [
            'ai_incident_id' => $incident->id,
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'Test cause',
            'root_causes' => 'Test root causes',
            'recommendations' => 'Test recommendations',
            'lead_analyst' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rca_method']);
    }

    public function test_store_validates_rca_method_enum(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'invalid_method',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'Test cause',
            'root_causes' => 'Test root causes',
            'recommendations' => 'Test recommendations',
            'lead_analyst' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rca_method']);
    }

    public function test_store_validates_immediate_cause_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'root_causes' => 'Test root causes',
            'recommendations' => 'Test recommendations',
            'lead_analyst' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['immediate_cause']);
    }

    public function test_store_validates_root_causes_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'Test cause',
            'recommendations' => 'Test recommendations',
            'lead_analyst' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['root_causes']);
    }

    public function test_store_validates_recommendations_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'Test cause',
            'root_causes' => 'Test root causes',
            'lead_analyst' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recommendations']);
    }

    public function test_store_validates_lead_analyst_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'Test cause',
            'root_causes' => 'Test root causes',
            'recommendations' => 'Test recommendations',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lead_analyst']);
    }

    public function test_store_validates_report_link_is_url(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
            'analysis_date' => $analysisDate->toDateTimeString(),
            'immediate_cause' => 'Test cause',
            'root_causes' => 'Test root causes',
            'recommendations' => 'Test recommendations',
            'lead_analyst' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
            'report_link' => 'not-a-valid-url',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['report_link']);
    }

    public function test_store_accepts_all_valid_rca_methods(): void
    {
        $incident = AiIncident::factory()->create();
        $rcaMethods = ['five_whys', 'fishbone', 'fault_tree', 'event_causal', 'change', 'timeline', 'barrier', 'combined'];
        $analysisDate = now();

        foreach ($rcaMethods as $method) {
            $data = [
                'ai_incident_id' => $incident->id,
                'rca_method' => $method,
                'analysis_date' => $analysisDate->toDateTimeString(),
                'immediate_cause' => "Test cause for {$method}",
                'root_causes' => 'Test root causes',
                'recommendations' => 'Test recommendations',
                'lead_analyst' => 'John Doe',
                'approved_at' => now()->toDateTimeString(),
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/incident-root-cause-analyses', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.rca_method', $method);
        }
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/incident-root-cause-analyses', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_incident_root_cause_analysis(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/incident-root-cause-analyses/{$rca->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident root cause analysis retrieved successfully',
            ])
            ->assertJsonPath('data.id', $rca->id)
            ->assertJsonPath('data.rca_method', $rca->rca_method);
    }

    public function test_show_returns_404_for_non_existent_rca(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/incident-root-cause-analyses/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $response = $this->getJson("/api/incident-root-cause-analyses/{$rca->id}");

        $response->assertStatus(401);
    }

    public function test_update_updates_incident_root_cause_analysis(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'rca_method' => 'five_whys',
            'immediate_cause' => 'Original cause',
            'root_causes' => 'Original root causes',
        ]);

        $updateData = [
            'rca_method' => 'fishbone',
            'immediate_cause' => 'Updated cause',
            'root_causes' => 'Updated root causes',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-root-cause-analyses/{$rca->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident root cause analysis updated successfully',
            ])
            ->assertJsonPath('data.rca_method', 'fishbone')
            ->assertJsonPath('data.immediate_cause', 'Updated cause')
            ->assertJsonPath('data.root_causes', 'Updated root causes');

        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'id' => $rca->id,
            'rca_method' => 'fishbone',
            'immediate_cause' => 'Updated cause',
        ]);
    }

    public function test_update_partially_updates_incident_root_cause_analysis(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'rca_method' => 'five_whys',
            'contributing_factors' => null,
        ]);

        $updateData = [
            'contributing_factors' => 'Added contributing factors',
            'control_failures' => 'Added control failures',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-root-cause-analyses/{$rca->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.rca_method', 'five_whys')
            ->assertJsonPath('data.contributing_factors', 'Added contributing factors')
            ->assertJsonPath('data.control_failures', 'Added control failures');

        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'id' => $rca->id,
            'rca_method' => 'five_whys',
        ]);
    }

    public function test_update_requires_authentication(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $response = $this->postJson("/api/incident-root-cause-analyses/{$rca->id}", []);

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_incident_root_cause_analysis(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/incident-root-cause-analyses/{$rca->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident root cause analysis deleted successfully',
            ]);

        $this->assertDatabaseMissing('incident_root_cause_analyses', [
            'id' => $rca->id,
        ]);
    }

    public function test_destroy_returns_404_for_non_existent_rca(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/incident-root-cause-analyses/99999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $response = $this->deleteJson("/api/incident-root-cause-analyses/{$rca->id}");

        $response->assertStatus(401);
    }
}
