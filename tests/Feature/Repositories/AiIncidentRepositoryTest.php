<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\AiModel;
use App\Models\Dataset;
use App\Models\AiIncident;
use App\Models\Organization;
use App\Enums\AiIncident\Domain;
use App\Enums\AiIncident\IncidentType;
use App\Enums\AiIncident\ResponseTeam;
use App\Enums\AiIncident\ExternalParty;
use App\Enums\AiIncident\IncidentStatus;
use App\Enums\AiIncident\ImpactedDataType;
use App\Enums\AiIncident\IncidentSeverity;
use App\Repositories\AiIncidentRepository;
use App\Enums\AiIncident\ResidencyAffected;
use App\Enums\AiIncident\AffectedBusinessUnit;
use App\Enums\AiIncident\NotificationRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\AiIncident\PrimaryRegulatoryFramework;

class AiIncidentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AiIncidentRepository $repository;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AiIncidentRepository;
        $this->organization = Organization::factory()->create();
    }

    public function test_get_paginated_ai_incidents_returns_paginated_collection(): void
    {
        AiIncident::factory()->count(15)->create();

        $result = $this->repository->getFilteredAiIncident(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_get_paginated_ai_incidents_orders_by_created_at_desc(): void
    {
        $first = AiIncident::factory()->create(['created_at' => now()->subDays(2)]);
        $second = AiIncident::factory()->create(['created_at' => now()->subDay()]);
        $third = AiIncident::factory()->create(['created_at' => now()]);

        $result = $this->repository->getFilteredAiIncident(['per_page' => 10]);

        $this->assertEquals($third->id, $result->items()[0]->id);
        $this->assertEquals($second->id, $result->items()[1]->id);
        $this->assertEquals($first->id, $result->items()[2]->id);
    }

    public function test_create_ai_incident_creates_new_record(): void
    {
        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Test Incident',
            'summary' => 'This is a test incident summary.',
            'incident_type' => IncidentType::DATA_BREACH->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV1_CRITICAL->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'John Doe',
            'response_team' => ResponseTeam::INFORMATION_SECURITY->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertInstanceOf(AiIncident::class, $aiIncident);
        $this->assertEquals('Test Incident', $aiIncident->title);
        $this->assertEquals(IncidentType::DATA_BREACH->value, $aiIncident->incident_type);
        $this->assertIsArray($aiIncident->data_types_impacted);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_BREACH->value,
            'severity' => IncidentSeverity::SEV1_CRITICAL->value,
        ]);
    }

    public function test_create_ai_incident_with_all_fields(): void
    {
        $aiModel = AiModel::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Privacy Breach',
            'summary' => 'User data was exposed due to misconfiguration.',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::CONTAINED->value,
            'incident_commander' => 'Jane Smith',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_residency_affected' => ResidencyAffected::EU->value,
            'regulatory_reference' => 'GDPR Article 33',
            'estimated_impacted_users' => 1000,
            'estimated_impacted_records' => 5000,
            'data_types_impacted' => [
                ImpactedDataType::PII_DIRECT_IDENTIFIERS->value,
                ImpactedDataType::PII_FINANCIAL->value,
            ],
            'affected_business_units' => [
                AffectedBusinessUnit::CUSTOMER_SERVICE->value,
                AffectedBusinessUnit::IT_TECHNOLOGY->value,
            ],
            'external_parties_involved' => [
                ExternalParty::REGULATOR->value,
            ],
            'business_impact_description' => 'Critical business impact affecting customer trust',
            'impacted_systems' => 'Payment processing, User authentication',
            'ai_model_id' => $aiModel->id,
            'linked_dataset_id' => Dataset::factory()->create()->id,
            'linked_risk_id' => 456,
            'evidence_link' => 'https://evidence.example.com/incident-123',
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertEquals('Privacy Breach', $aiIncident->title);
        $this->assertEquals(IncidentType::PRIVACY_VIOLATION->value, $aiIncident->incident_type);
        $this->assertEquals($aiModel->id, $aiIncident->ai_model_id);
        $this->assertEquals(1000, $aiIncident->estimated_impacted_users);
        $this->assertIsArray($aiIncident->data_types_impacted);
        $this->assertContains(ImpactedDataType::PII_DIRECT_IDENTIFIERS->value, $aiIncident->data_types_impacted);
        $this->assertContains(ImpactedDataType::PII_FINANCIAL->value, $aiIncident->data_types_impacted);
        $this->assertIsArray($aiIncident->affected_business_units);
        $this->assertEquals('https://evidence.example.com/incident-123', $aiIncident->evidence_link);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'incident_commander' => 'Jane Smith',
            'linked_risk_id' => 456,
        ]);
    }

    public function test_update_ai_incident_updates_existing_record(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'title' => 'Original Title',
            'status' => IncidentStatus::OPEN->value,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => IncidentStatus::RESOLVED->value,
        ];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertInstanceOf(AiIncident::class, $updated);
        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals(IncidentStatus::RESOLVED->value, $updated->status);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'title' => 'Updated Title',
            'status' => IncidentStatus::RESOLVED->value,
        ]);
    }

    public function test_update_ai_incident_partial_update(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'title' => 'Test Incident',
            'domain' => Domain::INFORMATION_SECURITY->value,
            'severity' => IncidentSeverity::SEV3_MEDIUM->value,
        ]);

        $updateData = ['severity' => IncidentSeverity::SEV1_CRITICAL->value];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertEquals('Test Incident', $updated->title);
        $this->assertEquals(Domain::INFORMATION_SECURITY->value, $updated->domain);
        $this->assertEquals(IncidentSeverity::SEV1_CRITICAL->value, $updated->severity);
    }

    public function test_update_ai_incident_can_change_status(): void
    {
        $aiIncident = AiIncident::factory()->create(['status' => IncidentStatus::OPEN->value]);

        $updateData = ['status' => IncidentStatus::CLOSED->value];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertEquals(IncidentStatus::CLOSED->value, $updated->status);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'status' => IncidentStatus::CLOSED->value,
        ]);
    }

    public function test_update_ai_incident_can_change_incident_commander(): void
    {
        $aiIncident = AiIncident::factory()->create(['incident_commander' => 'John Doe']);

        $updateData = ['incident_commander' => 'Jane Smith'];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertEquals('Jane Smith', $updated->incident_commander);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'incident_commander' => 'Jane Smith',
        ]);
    }

    public function test_delete_ai_incident_removes_record(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $result = $this->repository->deleteAiIncident($aiIncident);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('ai_incidents', [
            'id' => $aiIncident->id,
        ]);
    }

    public function test_delete_ai_incident_returns_true_on_success(): void
    {
        $aiIncident = AiIncident::factory()->create();

        $result = $this->repository->deleteAiIncident($aiIncident);

        $this->assertTrue($result);
    }

    public function test_get_paginated_ai_incidents_handles_empty_results(): void
    {
        $result = $this->repository->getFilteredAiIncident(['per_page' => 10]);

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    public function test_update_ai_incident_can_update_all_fields(): void
    {
        $aiIncident = AiIncident::factory()->create();
        $newAiModel = AiModel::factory()->create();

        $updateData = [
            'title' => 'Completely Updated Title',
            'summary' => 'Completely updated summary.',
            'incident_type' => IncidentType::SECURITY_INCIDENT->value,
            'domain' => Domain::INFORMATION_SECURITY->value,
            'severity' => IncidentSeverity::SEV4_LOW->value,
            'status' => IncidentStatus::CLOSED->value,
            'incident_commander' => 'New Owner',
            'response_team' => ResponseTeam::LEGAL->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::SOX->value,
            'ai_model_id' => $newAiModel->id,
            'estimated_impacted_users' => 100,
            'data_types_impacted' => [ImpactedDataType::SPECIAL_CATEGORY_HEALTH->value],
            'impacted_systems' => 'Legacy system',
            'evidence_link' => 'https://new-evidence.example.com',
        ];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertEquals('Completely Updated Title', $updated->title);
        $this->assertEquals('Completely updated summary.', $updated->summary);
        $this->assertEquals(IncidentType::SECURITY_INCIDENT->value, $updated->incident_type);
        $this->assertEquals(Domain::INFORMATION_SECURITY->value, $updated->domain);
        $this->assertEquals(IncidentSeverity::SEV4_LOW->value, $updated->severity);
        $this->assertEquals(IncidentStatus::CLOSED->value, $updated->status);
        $this->assertEquals('New Owner', $updated->incident_commander);
        $this->assertEquals($newAiModel->id, $updated->ai_model_id);
        $this->assertEquals(100, $updated->estimated_impacted_users);
        $this->assertEquals([ImpactedDataType::SPECIAL_CATEGORY_HEALTH->value], $updated->data_types_impacted);
        $this->assertEquals('Legacy system', $updated->impacted_systems);
        $this->assertEquals('https://new-evidence.example.com', $updated->evidence_link);
    }

    public function test_create_ai_incident_with_model_relationships(): void
    {
        $aiModel = AiModel::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Model-Related Incident',
            'summary' => 'Incident involving specific model',
            'incident_type' => IncidentType::AI_MODEL_FAILURE->value,
            'domain' => Domain::AI_GOVERNANCE->value,
            'severity' => IncidentSeverity::SEV3_MEDIUM->value,
            'status' => IncidentStatus::INVESTIGATING->value,
            'incident_commander' => 'Test Owner',
            'response_team' => ResponseTeam::ML_ENGINEERING->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::EU_AI_ACT->value,
            'notification_requirement' => NotificationRequirement::INTERNAL_ONLY->value,
            'ai_model_id' => $aiModel->id,
            'data_types_impacted' => [ImpactedDataType::INTERNAL_DATA->value],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertEquals($aiModel->id, $aiIncident->ai_model_id);
        $this->assertEquals(IncidentType::AI_MODEL_FAILURE->value, $aiIncident->incident_type);
    }

    public function test_create_ai_incident_with_data_types_impacted_array(): void
    {
        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Data Impact Test',
            'summary' => 'Testing multiple data types',
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'Data Officer',
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DATA_SUBJECTS_REQUIRED->value,
            'data_types_impacted' => [
                ImpactedDataType::PII_DIRECT_IDENTIFIERS->value,
                ImpactedDataType::PII_FINANCIAL->value,
                ImpactedDataType::SPECIAL_CATEGORY_HEALTH->value,
            ],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertIsArray($aiIncident->data_types_impacted);
        $this->assertCount(3, $aiIncident->data_types_impacted);
        $this->assertContains(ImpactedDataType::PII_DIRECT_IDENTIFIERS->value, $aiIncident->data_types_impacted);
        $this->assertContains(ImpactedDataType::PII_FINANCIAL->value, $aiIncident->data_types_impacted);
        $this->assertContains(ImpactedDataType::SPECIAL_CATEGORY_HEALTH->value, $aiIncident->data_types_impacted);
    }

    public function test_update_ai_incident_can_update_data_types_impacted(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
        ]);

        $updateData = [
            'data_types_impacted' => [
                ImpactedDataType::PII_DIRECT_IDENTIFIERS->value,
                ImpactedDataType::PII_FINANCIAL->value,
                ImpactedDataType::INTERNAL_DATA->value,
            ],
        ];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertCount(3, $updated->data_types_impacted);
        $this->assertContains(ImpactedDataType::PII_DIRECT_IDENTIFIERS->value, $updated->data_types_impacted);
        $this->assertContains(ImpactedDataType::PII_FINANCIAL->value, $updated->data_types_impacted);
        $this->assertContains(ImpactedDataType::INTERNAL_DATA->value, $updated->data_types_impacted);
    }

    public function test_create_ai_incident_with_affected_business_units(): void
    {
        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Business Units Test',
            'summary' => 'Testing affected business units',
            'incident_type' => IncidentType::SYSTEM_OUTAGE->value,
            'domain' => Domain::INFORMATION_SECURITY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'Business Lead',
            'response_team' => ResponseTeam::EXECUTIVE_LEADERSHIP->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::MULTIPLE->value,
            'notification_requirement' => NotificationRequirement::INTERNAL_ONLY->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'affected_business_units' => [
                AffectedBusinessUnit::IT_TECHNOLOGY->value,
                AffectedBusinessUnit::CUSTOMER_SERVICE->value,
            ],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertIsArray($aiIncident->affected_business_units);
        $this->assertCount(2, $aiIncident->affected_business_units);
        $this->assertContains(AffectedBusinessUnit::IT_TECHNOLOGY->value, $aiIncident->affected_business_units);
        $this->assertContains(AffectedBusinessUnit::CUSTOMER_SERVICE->value, $aiIncident->affected_business_units);
    }

    public function test_create_ai_incident_with_external_parties(): void
    {
        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'External Parties Test',
            'summary' => 'Testing external parties involved',
            'incident_type' => IncidentType::DATA_BREACH->value,
            'domain' => Domain::DATA_PRIVACY->value,
            'severity' => IncidentSeverity::SEV1_CRITICAL->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'Legal Officer',
            'response_team' => ResponseTeam::LEGAL->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_72_HOURS->value,
            'data_types_impacted' => [ImpactedDataType::PII_DIRECT_IDENTIFIERS->value],
            'external_parties_involved' => [
                ExternalParty::REGULATOR->value,
                ExternalParty::AUDITOR->value,
            ],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertIsArray($aiIncident->external_parties_involved);
        $this->assertCount(2, $aiIncident->external_parties_involved);
        $this->assertContains(ExternalParty::REGULATOR->value, $aiIncident->external_parties_involved);
        $this->assertContains(ExternalParty::AUDITOR->value, $aiIncident->external_parties_involved);
    }

    public function test_create_ai_incident_with_regulatory_frameworks(): void
    {
        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Regulatory Framework Test',
            'summary' => 'Testing different regulatory frameworks',
            'incident_type' => IncidentType::UNAUTHORIZED_ACCESS->value,
            'domain' => Domain::INFORMATION_SECURITY->value,
            'severity' => IncidentSeverity::SEV2_HIGH->value,
            'status' => IncidentStatus::OPEN->value,
            'incident_commander' => 'Compliance Officer',
            'response_team' => ResponseTeam::COMPLIANCE->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::HIPAA->value,
            'notification_requirement' => NotificationRequirement::DPA_WITHIN_24_HOURS->value,
            'data_residency_affected' => ResidencyAffected::US->value,
            'data_types_impacted' => [ImpactedDataType::SPECIAL_CATEGORY_HEALTH->value],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertEquals(PrimaryRegulatoryFramework::HIPAA->value, $aiIncident->primary_regulatory_framework);
        $this->assertEquals(NotificationRequirement::DPA_WITHIN_24_HOURS->value, $aiIncident->notification_requirement);
        $this->assertEquals(ResidencyAffected::US->value, $aiIncident->data_residency_affected);
    }

    public function test_filter_ai_incidents_by_status(): void
    {
        AiIncident::factory()->create(['status' => IncidentStatus::OPEN->value]);
        AiIncident::factory()->create(['status' => IncidentStatus::CLOSED->value]);
        AiIncident::factory()->create(['status' => IncidentStatus::OPEN->value]);

        $result = $this->repository->getFilteredAiIncident([
            'status' => IncidentStatus::OPEN->value,
            'per_page' => 15,
        ]);

        $this->assertCount(2, $result->items());
    }

    public function test_filter_ai_incidents_by_severity(): void
    {
        AiIncident::factory()->create(['severity' => IncidentSeverity::SEV1_CRITICAL->value]);
        AiIncident::factory()->create(['severity' => IncidentSeverity::SEV4_LOW->value]);
        AiIncident::factory()->create(['severity' => IncidentSeverity::SEV1_CRITICAL->value]);

        $result = $this->repository->getFilteredAiIncident([
            'severity' => IncidentSeverity::SEV1_CRITICAL->value,
            'per_page' => 15,
        ]);

        $this->assertCount(2, $result->items());
    }

    public function test_filter_ai_incidents_by_organization(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        AiIncident::factory()->create(['organization_id' => $org1->id]);
        AiIncident::factory()->create(['organization_id' => $org2->id]);
        AiIncident::factory()->create(['organization_id' => $org1->id]);

        $result = $this->repository->getFilteredAiIncident([
            'organization_id' => $org1->id,
            'per_page' => 15,
        ]);

        $this->assertCount(2, $result->items());
    }
}
