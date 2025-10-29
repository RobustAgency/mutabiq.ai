<?php

namespace Tests\Feature;

use App\Models\AiIncident;
use App\Models\IncidentRootCauseAnalysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentRootCauseAnalysisControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
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
                            'ai_incident_id',
                            'rca_method',
                            'immediate_cause',
                            'latent_causes',
                            'contributing_factors',
                            'impact_assessment',
                            'fixes_implemented',
                            'lessons_learned',
                            'recommendations',
                            'approved_by',
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
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'immediate_cause' => 'Model returned incorrect predictions due to data drift',
            'latent_causes' => 'Lack of continuous monitoring and retraining processes',
            'lessons_learned' => 'Need to implement automated drift detection',
            'recommendations' => 'Set up quarterly retraining schedule and implement monitoring alerts',
            'approved_by' => 'John Doe',
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
            ->assertJsonPath('data.rca_method', '5_whys')
            ->assertJsonPath('data.immediate_cause', 'Model returned incorrect predictions due to data drift')
            ->assertJsonPath('data.approved_by', 'John Doe');

        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'approved_by' => 'John Doe',
        ]);
    }

    public function test_store_creates_incident_root_cause_analysis_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $approvedAt = now()->subDays(2);

        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fishbone',
            'immediate_cause' => 'API timeout caused model failures',
            'latent_causes' => 'Insufficient capacity planning and lack of load testing',
            'contributing_factors' => 'Unexpected traffic spike during product launch',
            'impact_assessment' => 'Affected 1000+ users, resulted in 30 min downtime',
            'fixes_implemented' => 'Added auto-scaling rules and circuit breakers',
            'lessons_learned' => 'Need better capacity planning and load testing before launches',
            'recommendations' => 'Implement chaos engineering practices and improve monitoring',
            'approved_by' => 'Jane Smith',
            'approved_at' => $approvedAt->toDateTimeString(),
            'report_link' => 'https://example.com/rca/full-report-123',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.rca_method', 'fishbone')
            ->assertJsonPath('data.immediate_cause', 'API timeout caused model failures')
            ->assertJsonPath('data.contributing_factors', 'Unexpected traffic spike during product launch')
            ->assertJsonPath('data.impact_assessment', 'Affected 1000+ users, resulted in 30 min downtime')
            ->assertJsonPath('data.fixes_implemented', 'Added auto-scaling rules and circuit breakers')
            ->assertJsonPath('data.report_link', 'https://example.com/rca/full-report-123');

        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fishbone',
            'approved_by' => 'Jane Smith',
        ]);
    }

    public function test_store_validates_ai_incident_id_is_required(): void
    {
        $data = [
            'rca_method' => '5_whys',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_incident_id']);
    }

    public function test_store_validates_ai_incident_id_exists(): void
    {
        $data = [
            'ai_incident_id' => 99999,
            'rca_method' => '5_whys',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'John Doe',
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
        $data = [
            'ai_incident_id' => $incident->id,
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'John Doe',
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
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'invalid_method',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'John Doe',
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
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['immediate_cause']);
    }

    public function test_store_validates_latent_causes_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'immediate_cause' => 'Test cause',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latent_causes']);
    }

    public function test_store_validates_lessons_learned_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lessons_learned']);
    }

    public function test_store_validates_recommendations_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'approved_by' => 'John Doe',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recommendations']);
    }

    public function test_store_validates_approved_by_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['approved_by']);
    }

    public function test_store_validates_approved_at_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'John Doe',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-root-cause-analyses', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['approved_at']);
    }

    public function test_store_validates_report_link_is_url(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'John Doe',
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
        $rcaMethods = ['5_whys', 'fishbone', 'timeline_analysis', 'fault_tree', 'other'];

        foreach ($rcaMethods as $method) {
            $data = [
                'ai_incident_id' => $incident->id,
                'rca_method' => $method,
                'immediate_cause' => "Test cause for {$method}",
                'latent_causes' => 'Test latent causes',
                'lessons_learned' => 'Test lessons',
                'recommendations' => 'Test recommendations',
                'approved_by' => 'John Doe',
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
            'rca_method' => '5_whys',
            'immediate_cause' => 'Original cause',
            'lessons_learned' => 'Original lessons',
        ]);

        $updateData = [
            'rca_method' => 'fishbone',
            'immediate_cause' => 'Updated cause',
            'lessons_learned' => 'Updated lessons',
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
            ->assertJsonPath('data.lessons_learned', 'Updated lessons');

        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'id' => $rca->id,
            'rca_method' => 'fishbone',
            'immediate_cause' => 'Updated cause',
        ]);
    }

    public function test_update_partially_updates_incident_root_cause_analysis(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'rca_method' => '5_whys',
            'contributing_factors' => null,
        ]);

        $updateData = [
            'contributing_factors' => 'Added contributing factors',
            'impact_assessment' => 'Added impact assessment',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-root-cause-analyses/{$rca->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.rca_method', '5_whys')
            ->assertJsonPath('data.contributing_factors', 'Added contributing factors')
            ->assertJsonPath('data.impact_assessment', 'Added impact assessment');

        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'id' => $rca->id,
            'rca_method' => '5_whys',
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
