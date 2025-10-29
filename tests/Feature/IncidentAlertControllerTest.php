<?php

namespace Tests\Feature;

use App\Models\AiIncident;
use App\Models\IncidentAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentAlertControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_paginated_incident_alerts(): void
    {
        IncidentAlert::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/incident-alerts');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident alerts retrieved successfully',
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
                            'source_type',
                            'source_ref',
                            'rule_version',
                            'context',
                            'first_seen_at',
                            'last_seen_at',
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

    public function test_store_creates_incident_alert_with_required_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'source_type' => 'kri',
            'first_seen_at' => now()->subHour()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-alerts', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Incident alert created successfully',
            ])
            ->assertJsonPath('data.ai_incident_id', $incident->id)
            ->assertJsonPath('data.source_type', 'kri');

        $this->assertDatabaseHas('incident_alerts', [
            'ai_incident_id' => $incident->id,
            'source_type' => 'kri',
        ]);
    }

    public function test_store_creates_incident_alert_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $firstSeenAt = now()->subHours(2);
        $lastSeenAt = now()->subHour();

        $data = [
            'ai_incident_id' => $incident->id,
            'source_type' => 'monitoring_rule',
            'source_ref' => 'RULE-1234',
            'rule_version' => 'v2.1.0',
            'context' => 'Test context for monitoring rule alert',
            'first_seen_at' => $firstSeenAt->toDateTimeString(),
            'last_seen_at' => $lastSeenAt->toDateTimeString(),
            'evidence_link' => 'https://example.com/evidence/123',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-alerts', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.source_type', 'monitoring_rule')
            ->assertJsonPath('data.source_ref', 'RULE-1234')
            ->assertJsonPath('data.rule_version', 'v2.1.0')
            ->assertJsonPath('data.context', 'Test context for monitoring rule alert')
            ->assertJsonPath('data.evidence_link', 'https://example.com/evidence/123');

        $this->assertDatabaseHas('incident_alerts', [
            'ai_incident_id' => $incident->id,
            'source_ref' => 'RULE-1234',
            'rule_version' => 'v2.1.0',
        ]);
    }

    public function test_store_validates_ai_incident_id_is_required(): void
    {
        $data = [
            'source_type' => 'kri',
            'first_seen_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-alerts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_incident_id']);
    }

    public function test_store_validates_ai_incident_id_exists(): void
    {
        $data = [
            'ai_incident_id' => 99999,
            'source_type' => 'kri',
            'first_seen_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-alerts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_incident_id']);
    }

    public function test_store_validates_source_type_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'first_seen_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-alerts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_type']);
    }

    public function test_store_validates_source_type_enum(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'source_type' => 'invalid_source',
            'first_seen_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-alerts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_type']);
    }

    public function test_store_validates_first_seen_at_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'source_type' => 'kri',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-alerts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_seen_at']);
    }

    public function test_store_validates_last_seen_at_after_or_equal_first_seen_at(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'source_type' => 'kri',
            'first_seen_at' => now()->toDateTimeString(),
            'last_seen_at' => now()->subHour()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-alerts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_seen_at']);
    }

    public function test_store_validates_evidence_link_is_url(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'source_type' => 'kri',
            'first_seen_at' => now()->toDateTimeString(),
            'evidence_link' => 'not-a-valid-url',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-alerts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['evidence_link']);
    }

    public function test_store_accepts_all_valid_source_types(): void
    {
        $incident = AiIncident::factory()->create();
        $sourceTypes = ['kri', 'monitoring_rule', 'human_report', 'vendor_notice', 'security_tool', 'other'];

        foreach ($sourceTypes as $sourceType) {
            $data = [
                'ai_incident_id' => $incident->id,
                'source_type' => $sourceType,
                'first_seen_at' => now()->toDateTimeString(),
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/incident-alerts', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.source_type', $sourceType);
        }
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/incident-alerts', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_incident_alert(): void
    {
        $alert = IncidentAlert::factory()->create([
            'source_type' => 'kri',
            'source_ref' => 'KRI-123',
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/incident-alerts/{$alert->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident alert retrieved successfully',
            ])
            ->assertJsonPath('data.id', $alert->id)
            ->assertJsonPath('data.source_type', 'kri')
            ->assertJsonPath('data.source_ref', 'KRI-123');
    }

    public function test_show_returns_404_for_non_existent_alert(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/incident-alerts/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $alert = IncidentAlert::factory()->create();

        $response = $this->getJson("/api/incident-alerts/{$alert->id}");

        $response->assertStatus(401);
    }

    public function test_update_modifies_incident_alert(): void
    {
        $alert = IncidentAlert::factory()->create([
            'source_type' => 'kri',
            'source_ref' => 'OLD-REF',
        ]);

        $updateData = [
            'source_type' => 'monitoring_rule',
            'source_ref' => 'NEW-REF',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-alerts/{$alert->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident alert updated successfully',
            ])
            ->assertJsonPath('data.source_type', 'monitoring_rule')
            ->assertJsonPath('data.source_ref', 'NEW-REF');

        $this->assertDatabaseHas('incident_alerts', [
            'id' => $alert->id,
            'source_type' => 'monitoring_rule',
            'source_ref' => 'NEW-REF',
        ]);
    }

    public function test_update_supports_partial_updates(): void
    {
        $alert = IncidentAlert::factory()->create([
            'source_type' => 'kri',
            'source_ref' => 'KRI-123',
            'rule_version' => 'v1.0.0',
        ]);

        $updateData = ['rule_version' => 'v2.0.0'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-alerts/{$alert->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.source_type', 'kri')
            ->assertJsonPath('data.source_ref', 'KRI-123')
            ->assertJsonPath('data.rule_version', 'v2.0.0');
    }

    public function test_update_validates_source_type_enum(): void
    {
        $alert = IncidentAlert::factory()->create();

        $updateData = ['source_type' => 'invalid_source'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-alerts/{$alert->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_type']);
    }

    public function test_update_validates_evidence_link_is_url(): void
    {
        $alert = IncidentAlert::factory()->create();

        $updateData = ['evidence_link' => 'not-a-url'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-alerts/{$alert->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['evidence_link']);
    }

    public function test_update_requires_authentication(): void
    {
        $alert = IncidentAlert::factory()->create();

        $response = $this->postJson("/api/incident-alerts/{$alert->id}", []);

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_incident_alert(): void
    {
        $alert = IncidentAlert::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/incident-alerts/{$alert->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident alert deleted successfully',
            ]);

        $this->assertDatabaseMissing('incident_alerts', [
            'id' => $alert->id,
        ]);
    }

    public function test_destroy_returns_404_for_non_existent_alert(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/incident-alerts/99999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $alert = IncidentAlert::factory()->create();

        $response = $this->deleteJson("/api/incident-alerts/{$alert->id}");

        $response->assertStatus(401);
    }
}
