<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Models\AiIncident;
use App\Models\Organization;
use App\Enums\AiIncident\Domain;
use App\Enums\AiIncident\IncidentType;
use App\Enums\AiIncident\ResponseTeam;
use App\Enums\AiIncident\IncidentStatus;
use App\Enums\AiIncident\ImpactedDataType;
use App\Enums\AiIncident\IncidentSeverity;
use App\Enums\AiIncident\NotificationRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\AiIncident\PrimaryRegulatoryFramework;

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
        AiIncident::factory()->count(15)->create(['organization_id' => $this->organization->id]);

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
                            'display_id',
                            'organization_id',
                            'title',
                            'summary',
                            'incident_type',
                            'domain',
                            'severity',
                            'status',
                            'incident_commander',
                            'response_team',
                            'primary_regulatory_framework',
                            'notification_requirement',
                            'data_residency_affected',
                            'regulatory_reference',
                            'estimated_impacted_users',
                            'estimated_impacted_records',
                            'data_types_impacted',
                            'affected_business_units',
                            'external_parties_involved',
                            'business_impact_description',
                            'impacted_systems',
                            'ai_model_id',
                            'linked_dataset_id',
                            'linked_risk_id',
                            'linked_assessment_id',
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

    public function test_index_returns_default_pagination(): void
    {
        AiIncident::factory()->count(20)->create(['organization_id' => $this->organization->id]);

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

        $data = [
            'title' => 'Test Privacy Incident',
            'summary' => 'This is a detailed summary of the privacy incident.',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV1_CRITICAL->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'estimated_impacted_users' => 1000,
            'estimated_impacted_records' => 5000,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'impacted_systems' => 'Payment system, User database',
            'ai_model_id' => $aiModel->id,
            'evidence_link' => 'https://evidence.example.com/incident-123',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'AI incident created successfully',
            ])
            ->assertJsonPath('data.title', 'Test Privacy Incident')
            ->assertJsonPath('data.incident_type', IncidentType::PRIVACY_VIOLATION->value)
            ->assertJsonPath('data.domain', Domain::DATA_PRIVACY->value)
            ->assertJsonPath('data.severity', IncidentSeverity::SEV1_CRITICAL->value)
            ->assertJsonPath('data.status', IncidentStatus::OPEN->value)
            ->assertJsonPath('data.incident_commander', 'John Doe')
            ->assertJsonPath('data.ai_model_id', $aiModel->id)
            ->assertJsonPath('data.estimated_impacted_users', 1000)
            ->assertJsonPath('data.evidence_link', 'https://evidence.example.com/incident-123');

        $this->assertDatabaseHas('ai_incidents', [
            'title' => 'Test Privacy Incident',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'severity' => IncidentSeverity::SEV1_CRITICAL->value,
            'ai_model_id' => $aiModel->id,
        ]);
    }

    public function test_store_validates_title_is_required(): void
    {
        $data = [
            'summary' => 'Test summary',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
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
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['summary']);
    }

    public function test_store_validates_incident_type_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['incident_type']);
    }

    public function test_store_validates_incident_type_enum(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'incident_type' => 'invalid_type',
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['incident_type']);
    }

    public function test_store_validates_domain_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['domain']);
    }

    public function test_store_validates_domain_enum(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => 'invalid_domain',
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['domain']);
    }

    public function test_store_validates_data_types_impacted_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'estimated_impacted_records' => 100,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data_types_impacted']);
    }

    public function test_store_validates_estimated_impacted_records_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['estimated_impacted_records']);
    }

    public function test_store_validates_severity_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
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
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => 'invalid_severity',
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
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
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
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
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => 'invalid_status',
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_validates_incident_commander_is_required(): void
    {
        $data = [
            'title' => 'Test Incident',
            'summary' => 'Test summary',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'estimated_impacted_records' => 100,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-incidents', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['incident_commander']);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/ai-incidents', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_ai_incident(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Test Incident',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
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
            ->assertJsonPath('data.incident_type', IncidentType::PRIVACY_VIOLATION->value);
    }

    public function test_show_returns_404_for_non_existent_ai_incident(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-incidents/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $aiIncident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson("/api/ai-incidents/{$aiIncident->id}");

        $response->assertStatus(401);
    }

    public function test_update_modifies_ai_incident(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Original Title',
            'status' => IncidentStatus::OPEN->value,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => IncidentStatus::CLOSED->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI incident updated successfully',
            ])
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.status', IncidentStatus::CLOSED->value);

        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'title' => 'Updated Title',
            'status' => IncidentStatus::CLOSED->value,
        ]);
    }

    public function test_update_supports_partial_updates(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Test Incident',
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV3_MEDIUM->value,
        ]);

        $updateData = ['severity' => IncidentSeverity::SEV1_CRITICAL->value];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Test Incident')
            ->assertJsonPath('data.domain', Domain::DATA_PRIVACY->value)
            ->assertJsonPath('data.severity', IncidentSeverity::SEV1_CRITICAL->value);
    }

    public function test_update_can_change_status(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => IncidentStatus::OPEN->value,
        ]);

        $updateData = ['status' => IncidentStatus::CLOSED->value];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', IncidentStatus::CLOSED->value);

        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'status' => IncidentStatus::CLOSED->value,
        ]);
    }

    public function test_update_can_change_incident_commander(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'organization_id' => $this->organization->id,
            'incident_commander' => 'John Doe',
        ]);

        $updateData = ['incident_commander' => 'Jane Smith'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.incident_commander', 'Jane Smith');

        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'incident_commander' => 'Jane Smith',
        ]);
    }

    public function test_update_validates_incident_type_enum(): void
    {
        $aiIncident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = ['incident_type' => 'invalid_type'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['incident_type']);
    }

    public function test_update_validates_domain_enum(): void
    {
        $aiIncident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = ['domain' => 'invalid_domain'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['domain']);
    }

    public function test_update_validates_severity_enum(): void
    {
        $aiIncident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = ['severity' => 'invalid_severity'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['severity']);
    }

    public function test_update_validates_status_enum(): void
    {
        $aiIncident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = ['status' => 'invalid_status'];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-incidents/{$aiIncident->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_requires_authentication(): void
    {
        $aiIncident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->postJson("/api/ai-incidents/{$aiIncident->id}", []);

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_ai_incident(): void
    {
        $aiIncident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);

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
        $aiIncident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->deleteJson("/api/ai-incidents/{$aiIncident->id}");

        $response->assertStatus(401);
    }

    public function test_store_accepts_all_valid_incident_types(): void
    {
        $incidentTypes = [
            IncidentType::AI_MODEL_FAILURE->value,
            IncidentType::DATA_BREACH->value,
            IncidentType::PRIVACY_VIOLATION->value,
            IncidentType::SECURITY_INCIDENT->value,
        ];

        foreach ($incidentTypes as $incidentType) {
            $data = [
                'title' => "Test {$incidentType} Incident",
                'summary' => 'Test summary',
                'incident_type' => $incidentType,
                'domain' => Domain::DATA_PRIVACY->value,
                'severity' => IncidentSeverity::SEV2_HIGH->value,
                'status' => IncidentStatus::OPEN->value,
                'incident_commander' => 'Test Owner',
                'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
                'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
                'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
                'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
                'estimated_impacted_records' => 100,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-incidents', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.incident_type', $incidentType);
        }
    }

    public function test_store_accepts_all_valid_severities(): void
    {
        $severities = [
            IncidentSeverity::SEV1_CRITICAL->value,
            IncidentSeverity::SEV2_HIGH->value,
            IncidentSeverity::SEV3_MEDIUM->value,
            IncidentSeverity::SEV4_LOW->value,
        ];

        foreach ($severities as $severity) {
            $data = [
                'title' => "Test {$severity} Incident",
                'summary' => 'Test summary',
                'incident_type' => IncidentType::DATA_BREACH->value,
                'domain' => Domain::DATA_PRIVACY->value,
                'severity' => $severity,
                'status' => IncidentStatus::OPEN->value,
                'incident_commander' => 'Test Owner',
                'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
                'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
                'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
                'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
                'estimated_impacted_records' => 100,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-incidents', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.severity', $severity);
        }
    }

    public function test_store_accepts_all_valid_statuses(): void
    {
        $statuses = [
            IncidentStatus::OPEN->value,
            IncidentStatus::INVESTIGATING->value,
            IncidentStatus::CONTAINED->value,
            IncidentStatus::CLOSED->value,
        ];

        foreach ($statuses as $status) {
            $data = [
                'title' => "Test {$status} Incident",
                'summary' => 'Test summary',
                'incident_type' => IncidentType::DATA_BREACH->value,
                'domain' => Domain::DATA_PRIVACY->value,
                'severity' => IncidentSeverity::SEV2_HIGH->value,
                'status' => $status,
                'incident_commander' => 'Test Owner',
                'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
                'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
                'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
                'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
                'estimated_impacted_records' => 100,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-incidents', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.status', $status);
        }
    }

    public function test_store_accepts_all_valid_domains(): void
    {
        $domains = [
            Domain::AI_GOVERNANCE->value,
            Domain::DATA_PRIVACY->value,
            Domain::DATA_GOVERNANCE->value,
            Domain::INFORMATION_SECURITY->value,
        ];

        foreach ($domains as $domain) {
            $data = [
                'title' => "Test {$domain} Incident",
                'summary' => 'Test summary',
                'incident_type' => IncidentType::DATA_BREACH->value,
                'domain' => $domain,
                'severity' => IncidentSeverity::SEV2_HIGH->value,
                'status' => IncidentStatus::OPEN->value,
                'incident_commander' => 'Test Owner',
                'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
                'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
                'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
                'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
                'estimated_impacted_records' => 100,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-incidents', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.domain', $domain);
        }
    }
}
