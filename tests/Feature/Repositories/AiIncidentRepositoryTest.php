<?php

namespace Tests\Feature\Repositories;

use App\Models\AiIncident;
use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\UseCase;
use App\Repositories\AiIncidentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;

class AiIncidentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AiIncidentRepository $repository;
    private $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AiIncidentRepository();
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
            'category' => 'safety',
            'severity' => 'sev1_critical',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'John Doe',
            'first_seen_at' => now()->subHours(2),
            'declared_at' => now()->subHour(),
            'impacted_data' => ['pii', 'sensitive_personal'],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertInstanceOf(AiIncident::class, $aiIncident);
        $this->assertEquals('Test Incident', $aiIncident->title);
        $this->assertEquals('safety', $aiIncident->category);
        $this->assertIsArray($aiIncident->impacted_data);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'title' => 'Test Incident',
            'category' => 'safety',
            'severity' => 'sev1_critical',
        ]);
    }

    public function test_create_ai_incident_with_all_fields(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create();
        $useCase = UseCase::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Privacy Breach',
            'summary' => 'User data was exposed due to misconfiguration.',
            'category' => 'privacy',
            'severity' => 'sev2_high',
            'status' => 'contained',
            'stage' => 'staging',
            'ic_owner' => 'Jane Smith',
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'use_case_id' => $useCase->id,
            'first_seen_at' => now()->subDays(2),
            'declared_at' => now()->subDay(),
            'resolved_at' => now()->subHours(6),
            'closed_at' => now()->subHours(2),
            'impacted_users' => '1000+ users',
            'impacted_data' => ['pii', 'financial'],
            'impacted_systems' => 'Payment processing, User authentication',
            'linked_release_id' => 'REL-123',
            'linked_risk_id' => 'RISK-456',
            'linked_assessment_id' => 'ASS-789',
            'linked_capa_id' => 'CAPA-101',
            'evidence_link' => 'https://evidence.example.com/incident-123',
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertEquals('Privacy Breach', $aiIncident->title);
        $this->assertEquals('privacy', $aiIncident->category);
        $this->assertEquals($aiModel->id, $aiIncident->ai_model_id);
        $this->assertEquals($aiModelVersion->id, $aiIncident->ai_model_version_id);
        $this->assertEquals($useCase->id, $aiIncident->use_case_id);
        $this->assertEquals('1000+ users', $aiIncident->impacted_users);
        $this->assertIsArray($aiIncident->impacted_data);
        $this->assertContains('pii', $aiIncident->impacted_data);
        $this->assertContains('financial', $aiIncident->impacted_data);
        $this->assertEquals('https://evidence.example.com/incident-123', $aiIncident->evidence_link);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'ic_owner' => 'Jane Smith',
            'linked_release_id' => 'REL-123',
        ]);
    }

    public function test_update_ai_incident_updates_existing_record(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'title' => 'Original Title',
            'status' => 'open',
            'resolved_at' => null,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => 'resolved',
            'resolved_at' => now(),
        ];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertInstanceOf(AiIncident::class, $updated);
        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals('resolved', $updated->status);
        $this->assertNotNull($updated->resolved_at);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'title' => 'Updated Title',
            'status' => 'resolved',
        ]);
    }

    public function test_update_ai_incident_partial_update(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'title' => 'Test Incident',
            'category' => 'security',
            'severity' => 'sev3_medium',
        ]);

        $updateData = ['severity' => 'sev1_critical'];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertEquals('Test Incident', $updated->title);
        $this->assertEquals('security', $updated->category);
        $this->assertEquals('sev1_critical', $updated->severity);
    }

    public function test_update_ai_incident_can_change_status(): void
    {
        $aiIncident = AiIncident::factory()->create(['status' => 'open']);

        $updateData = ['status' => 'closed'];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertEquals('closed', $updated->status);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'status' => 'closed',
        ]);
    }

    public function test_update_ai_incident_can_change_ic_owner(): void
    {
        $aiIncident = AiIncident::factory()->create(['ic_owner' => 'John Doe']);

        $updateData = ['ic_owner' => 'Jane Smith'];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertEquals('Jane Smith', $updated->ic_owner);
        $this->assertDatabaseHas('ai_incidents', [
            'id' => $aiIncident->id,
            'ic_owner' => 'Jane Smith',
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
            'category' => 'vendor',
            'severity' => 'near_miss',
            'status' => 'closed',
            'stage' => 'retirement',
            'ic_owner' => 'New Owner',
            'ai_model_id' => $newAiModel->id,
            'impacted_users' => 'internal only',
            'impacted_data' => ['none'],
            'impacted_systems' => 'Legacy system',
            'evidence_link' => 'https://new-evidence.example.com',
        ];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertEquals('Completely Updated Title', $updated->title);
        $this->assertEquals('Completely updated summary.', $updated->summary);
        $this->assertEquals('vendor', $updated->category);
        $this->assertEquals('near_miss', $updated->severity);
        $this->assertEquals('closed', $updated->status);
        $this->assertEquals('retirement', $updated->stage);
        $this->assertEquals('New Owner', $updated->ic_owner);
        $this->assertEquals($newAiModel->id, $updated->ai_model_id);
        $this->assertEquals('internal only', $updated->impacted_users);
        $this->assertEquals(['none'], $updated->impacted_data);
        $this->assertEquals('Legacy system', $updated->impacted_systems);
        $this->assertEquals('https://new-evidence.example.com', $updated->evidence_link);
    }

    public function test_create_ai_incident_with_model_relationships(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create();
        $useCase = UseCase::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Model-Related Incident',
            'summary' => 'Incident involving specific model',
            'category' => 'reliability',
            'severity' => 'sev3_medium',
            'status' => 'monitoring',
            'stage' => 'prod',
            'ic_owner' => 'Test Owner',
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'use_case_id' => $useCase->id,
            'first_seen_at' => now()->subHour(),
            'declared_at' => now(),
            'impacted_data' => ['unknown'],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertEquals($aiModel->id, $aiIncident->ai_model_id);
        $this->assertEquals($aiModelVersion->id, $aiIncident->ai_model_version_id);
        $this->assertEquals($useCase->id, $aiIncident->use_case_id);
    }

    public function test_create_ai_incident_with_impacted_data_array(): void
    {
        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Data Impact Test',
            'summary' => 'Testing multiple data types',
            'category' => 'privacy',
            'severity' => 'sev2_high',
            'status' => 'open',
            'stage' => 'prod',
            'ic_owner' => 'Data Officer',
            'first_seen_at' => now()->subHour(),
            'declared_at' => now(),
            'impacted_data' => ['pii', 'financial', 'health'],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertIsArray($aiIncident->impacted_data);
        $this->assertCount(3, $aiIncident->impacted_data);
        $this->assertContains('pii', $aiIncident->impacted_data);
        $this->assertContains('financial', $aiIncident->impacted_data);
        $this->assertContains('health', $aiIncident->impacted_data);
    }

    public function test_update_ai_incident_can_update_impacted_data(): void
    {
        $aiIncident = AiIncident::factory()->create([
            'impacted_data' => ['pii'],
        ]);

        $updateData = [
            'impacted_data' => ['pii', 'financial', 'sensitive_personal'],
        ];

        $updated = $this->repository->updateAiIncident($aiIncident, $updateData);

        $this->assertCount(3, $updated->impacted_data);
        $this->assertContains('pii', $updated->impacted_data);
        $this->assertContains('financial', $updated->impacted_data);
        $this->assertContains('sensitive_personal', $updated->impacted_data);
    }

    public function test_create_ai_incident_with_datetime_fields(): void
    {
        $firstSeen = now()->subDays(3);
        $declared = now()->subDays(2);
        $resolved = now()->subDay();
        $closed = now();

        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Datetime Test',
            'summary' => 'Testing datetime fields',
            'category' => 'security',
            'severity' => 'sev1_critical',
            'status' => 'closed',
            'stage' => 'prod',
            'ic_owner' => 'Security Lead',
            'first_seen_at' => $firstSeen,
            'declared_at' => $declared,
            'resolved_at' => $resolved,
            'closed_at' => $closed,
            'impacted_data' => ['pii'],
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertEquals($firstSeen->toDateTimeString(), $aiIncident->first_seen_at->toDateTimeString());
        $this->assertEquals($declared->toDateTimeString(), $aiIncident->declared_at->toDateTimeString());
        $this->assertEquals($resolved->toDateTimeString(), $aiIncident->resolved_at->toDateTimeString());
        $this->assertEquals($closed->toDateTimeString(), $aiIncident->closed_at->toDateTimeString());
    }

    public function test_create_ai_incident_with_linked_ids(): void
    {
        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Linked Records Test',
            'summary' => 'Testing linked IDs',
            'category' => 'other',
            'severity' => 'sev4_low',
            'status' => 'open',
            'stage' => 'test',
            'ic_owner' => 'Test Manager',
            'first_seen_at' => now()->subHour(),
            'declared_at' => now(),
            'impacted_data' => ['none'],
            'linked_release_id' => 'REL-999',
            'linked_risk_id' => 'RISK-888',
            'linked_assessment_id' => 'ASS-777',
            'linked_capa_id' => 'CAPA-666',
        ];

        $aiIncident = $this->repository->createAiIncident($data);

        $this->assertEquals('REL-999', $aiIncident->linked_release_id);
        $this->assertEquals('RISK-888', $aiIncident->linked_risk_id);
        $this->assertEquals('ASS-777', $aiIncident->linked_assessment_id);
        $this->assertEquals('CAPA-666', $aiIncident->linked_capa_id);
    }
}
