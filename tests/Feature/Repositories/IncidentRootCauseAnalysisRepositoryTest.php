<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AiIncident;
use App\Models\Organization;
use App\Models\IncidentRootCauseAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\IncidentRootCauseAnalysisRepository;

class IncidentRootCauseAnalysisRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected IncidentRootCauseAnalysisRepository $repository;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(IncidentRootCauseAnalysisRepository::class);
        $this->organization = Organization::factory()->create();
    }

    public function test_get_paginated_incident_root_cause_analyses_returns_paginated_results(): void
    {
        IncidentRootCauseAnalysis::factory()->count(25)->create();

        $result = $this->repository->getFilteredIncidentRootCauseAnalyses(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_create_incident_root_cause_analysis_creates_new_record(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now()->subDays(5);
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
            'analysis_date' => $analysisDate,
            'immediate_cause' => 'Model returned incorrect predictions',
            'root_causes' => 'Lack of continuous monitoring',
            'recommendations' => 'Implement monitoring alerts',
            'lead_analyst' => 'John Doe',
            'approved_at' => now(),
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertInstanceOf(IncidentRootCauseAnalysis::class, $result);
        $this->assertEquals($incident->id, $result->ai_incident_id);
        $this->assertEquals('five_whys', $result->rca_method);
        $this->assertEquals('Model returned incorrect predictions', $result->immediate_cause);
        $this->assertEquals('John Doe', $result->lead_analyst);
        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'id' => $result->id,
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
        ]);
    }

    public function test_create_incident_root_cause_analysis_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now()->subDays(3);
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fishbone',
            'analysis_date' => $analysisDate,
            'immediate_cause' => 'API timeout caused model failures',
            'root_causes' => 'Insufficient capacity planning',
            'contributing_factors' => 'Unexpected traffic spike',
            'control_failures' => 'Monitoring alerts not triggered',
            'recommendations' => 'Implement chaos engineering',
            'lead_analyst' => 'Jane Smith',
            'review_committee' => 'John Doe | Sarah Johnson | Mike Chen',
            'approved_at' => now(),
            'report_link' => 'https://example.com/rca/123',
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertEquals('fishbone', $result->rca_method);
        $this->assertEquals('API timeout caused model failures', $result->immediate_cause);
        $this->assertEquals('Unexpected traffic spike', $result->contributing_factors);
        $this->assertEquals('Monitoring alerts not triggered', $result->control_failures);
        $this->assertEquals('Jane Smith', $result->lead_analyst);
        $this->assertEquals('John Doe | Sarah Johnson | Mike Chen', $result->review_committee);
        $this->assertEquals('https://example.com/rca/123', $result->report_link);
        $this->assertNotNull($result->approved_at);
    }

    public function test_update_incident_root_cause_analysis_updates_existing_record(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'rca_method' => 'five_whys',
            'immediate_cause' => 'Original cause',
            'root_causes' => 'Original root causes',
        ]);

        $result = $this->repository->updateIncidentRootCauseAnalysis($rca, [
            'rca_method' => 'fishbone',
            'immediate_cause' => 'Updated cause',
            'root_causes' => 'Updated root causes',
        ]);

        $this->assertEquals('fishbone', $result->rca_method);
        $this->assertEquals('Updated cause', $result->immediate_cause);
        $this->assertEquals('Updated root causes', $result->root_causes);
        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'id' => $rca->id,
            'rca_method' => 'fishbone',
            'immediate_cause' => 'Updated cause',
        ]);
    }

    public function test_update_incident_root_cause_analysis_can_update_all_fields(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();
        $newIncident = AiIncident::factory()->create();
        $newAnalysisDate = now()->subDays(10);

        $updateData = [
            'ai_incident_id' => $newIncident->id,
            'rca_method' => 'timeline',
            'analysis_date' => $newAnalysisDate,
            'immediate_cause' => 'New immediate cause',
            'root_causes' => 'New root causes',
            'contributing_factors' => 'New contributing factors',
            'control_failures' => 'New control failures',
            'recommendations' => 'New recommendations',
            'lead_analyst' => 'New Analyst',
            'review_committee' => 'Person A | Person B | Person C',
            'approved_at' => now()->addDay(),
            'report_link' => 'https://example.com/new-rca',
        ];

        $result = $this->repository->updateIncidentRootCauseAnalysis($rca, $updateData);

        $this->assertEquals($newIncident->id, $result->ai_incident_id);
        $this->assertEquals('timeline', $result->rca_method);
        $this->assertEquals('New immediate cause', $result->immediate_cause);
        $this->assertEquals('New root causes', $result->root_causes);
        $this->assertEquals('New contributing factors', $result->contributing_factors);
        $this->assertEquals('New control failures', $result->control_failures);
        $this->assertEquals('New recommendations', $result->recommendations);
        $this->assertEquals('New Analyst', $result->lead_analyst);
        $this->assertEquals('Person A | Person B | Person C', $result->review_committee);
        $this->assertEquals('https://example.com/new-rca', $result->report_link);
    }

    public function test_delete_incident_root_cause_analysis_removes_record(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $result = $this->repository->deleteIncidentRootCauseAnalysis($rca);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('incident_root_cause_analyses', ['id' => $rca->id]);
    }

    public function test_get_incident_root_cause_analysis_by_id_returns_rca(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();
        $result = $this->repository->getIncidentRootCauseAnalysisById($rca);

        $this->assertInstanceOf(IncidentRootCauseAnalysis::class, $result);
        $this->assertEquals($rca->id, $result->id);
    }

    public function test_create_incident_root_cause_analysis_with_datetime_field(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now()->subDays(5);
        $approvedAt = now()->subDays(2);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fault_tree',
            'analysis_date' => $analysisDate,
            'immediate_cause' => 'Test cause',
            'root_causes' => 'Test root causes',
            'recommendations' => 'Test recommendations',
            'lead_analyst' => 'Analyst Name',
            'approved_at' => $approvedAt,
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertInstanceOf(\Carbon\Carbon::class, $result->analysis_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result->approved_at);
        $this->assertEquals($analysisDate->format('Y-m-d H:i:s'), $result->analysis_date->format('Y-m-d H:i:s'));
        $this->assertEquals($approvedAt->format('Y-m-d H:i:s'), $result->approved_at->format('Y-m-d H:i:s'));
    }

    public function test_create_incident_root_cause_analysis_with_all_rca_methods(): void
    {
        $incident = AiIncident::factory()->create();
        $rcaMethods = ['five_whys', 'fishbone', 'fault_tree', 'event_causal', 'change', 'timeline', 'barrier', 'combined'];

        foreach ($rcaMethods as $method) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'rca_method' => $method,
                'analysis_date' => now(),
                'immediate_cause' => "Test cause for {$method}",
                'root_causes' => 'Test root causes',
                'recommendations' => 'Test recommendations',
                'lead_analyst' => 'John Doe',
                'approved_at' => now(),
            ];

            $result = $this->repository->createIncidentRootCauseAnalysis($data);

            $this->assertEquals($method, $result->rca_method);
        }
    }

    public function test_paginated_rca_loads_ai_incident_relationship(): void
    {
        IncidentRootCauseAnalysis::factory()->count(3)->create();

        $result = $this->repository->getFilteredIncidentRootCauseAnalyses(['per_page' => 10]);

        $this->assertTrue($result->items()[0]->relationLoaded('aiIncident'));
    }

    public function test_get_by_id_loads_ai_incident_relationship(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $result = $this->repository->getIncidentRootCauseAnalysisById($rca);

        $this->assertTrue($result->relationLoaded('aiIncident'));
    }

    public function test_update_incident_root_cause_analysis_returns_fresh_instance(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'immediate_cause' => 'Original cause',
        ]);

        $result = $this->repository->updateIncidentRootCauseAnalysis($rca, [
            'immediate_cause' => 'Updated cause',
        ]);

        $this->assertEquals('Updated cause', $result->immediate_cause);
        $this->assertNotSame($rca, $result);
    }

    public function test_create_incident_root_cause_analysis_without_optional_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'rca_method' => 'combined',
            'analysis_date' => now(),
            'immediate_cause' => 'Test cause',
            'root_causes' => 'Test root causes',
            'recommendations' => 'Test recommendations',
            'lead_analyst' => 'Analyst',
            'approved_at' => now(),
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertNull($result->contributing_factors);
        $this->assertNull($result->control_failures);
        $this->assertNull($result->review_committee);
        $this->assertNull($result->report_link);
    }

    public function test_create_incident_root_cause_analysis_with_long_text_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $longText = str_repeat('This is a very long description. ', 100);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'rca_method' => 'five_whys',
            'analysis_date' => now(),
            'immediate_cause' => $longText,
            'root_causes' => $longText,
            'recommendations' => $longText,
            'lead_analyst' => 'Analyst',
            'approved_at' => now(),
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertEquals($longText, $result->immediate_cause);
        $this->assertEquals($longText, $result->root_causes);
        $this->assertEquals($longText, $result->recommendations);
    }

    public function test_update_can_set_optional_fields_to_null(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'contributing_factors' => 'Some factors',
            'control_failures' => 'Some failures',
            'review_committee' => 'Person A | Person B',
            'report_link' => 'https://example.com/report',
        ]);

        $result = $this->repository->updateIncidentRootCauseAnalysis($rca, [
            'contributing_factors' => null,
            'control_failures' => null,
            'review_committee' => null,
            'report_link' => null,
        ]);

        $this->assertNull($result->contributing_factors);
        $this->assertNull($result->control_failures);
        $this->assertNull($result->review_committee);
        $this->assertNull($result->report_link);
    }

    public function test_create_incident_root_cause_analysis_with_complete_fishbone_analysis(): void
    {
        $incident = AiIncident::factory()->create();
        $analysisDate = now()->subDays(7);
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fishbone',
            'analysis_date' => $analysisDate,
            'immediate_cause' => 'Model deployment failed during production release',
            'root_causes' => 'Inadequate testing in staging environment, lack of rollback procedures, insufficient monitoring',
            'contributing_factors' => 'Time pressure to meet release deadline, limited DevOps resources',
            'control_failures' => 'Monitoring alerts not configured, missing rollback automation',
            'recommendations' => 'Mandatory staging approval gate, automated rollback on failure, pre-release monitoring checklist',
            'lead_analyst' => 'Chief Technology Officer',
            'review_committee' => 'Lead Engineer | QA Manager | DevOps Lead',
            'approved_at' => now(),
            'report_link' => 'https://example.com/rca/fishbone-deployment-failure',
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertEquals('fishbone', $result->rca_method);
        $this->assertStringContainsString('deployment failed', $result->immediate_cause);
        $this->assertStringContainsString('testing in staging', $result->root_causes);
        $this->assertStringContainsString('Time pressure', $result->contributing_factors);
        $this->assertStringContainsString('Monitoring alerts', $result->control_failures);
        $this->assertStringContainsString('Mandatory staging', $result->recommendations);
        $this->assertEquals('Chief Technology Officer', $result->lead_analyst);
    }
}
