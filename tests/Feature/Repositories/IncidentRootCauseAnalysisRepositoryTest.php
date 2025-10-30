<?php

namespace Tests\Unit;

use App\Models\AiIncident;
use App\Models\IncidentRootCauseAnalysis;
use App\Repositories\IncidentRootCauseAnalysisRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentRootCauseAnalysisRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected IncidentRootCauseAnalysisRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(IncidentRootCauseAnalysisRepository::class);
    }

    public function test_get_paginated_incident_root_cause_analyses_returns_paginated_results(): void
    {
        IncidentRootCauseAnalysis::factory()->count(25)->create();

        $result = $this->repository->getPaginatedIncidentRootCauseAnalyses(10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_create_incident_root_cause_analysis_creates_new_record(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'immediate_cause' => 'Model returned incorrect predictions',
            'latent_causes' => 'Lack of continuous monitoring',
            'lessons_learned' => 'Need automated drift detection',
            'recommendations' => 'Implement monitoring alerts',
            'approved_by' => 'John Doe',
            'approved_at' => now(),
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertInstanceOf(IncidentRootCauseAnalysis::class, $result);
        $this->assertEquals($incident->id, $result->ai_incident_id);
        $this->assertEquals('5_whys', $result->rca_method);
        $this->assertEquals('Model returned incorrect predictions', $result->immediate_cause);
        $this->assertEquals('John Doe', $result->approved_by);
        $this->assertDatabaseHas('incident_root_cause_analyses', [
            'id' => $result->id,
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
        ]);
    }

    public function test_create_incident_root_cause_analysis_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fishbone',
            'immediate_cause' => 'API timeout caused model failures',
            'latent_causes' => 'Insufficient capacity planning',
            'contributing_factors' => 'Unexpected traffic spike',
            'impact_assessment' => 'Affected 1000+ users',
            'fixes_implemented' => 'Added auto-scaling rules',
            'lessons_learned' => 'Need better capacity planning',
            'recommendations' => 'Implement chaos engineering',
            'approved_by' => 'Jane Smith',
            'approved_at' => now(),
            'report_link' => 'https://example.com/rca/123',
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertEquals('fishbone', $result->rca_method);
        $this->assertEquals('API timeout caused model failures', $result->immediate_cause);
        $this->assertEquals('Unexpected traffic spike', $result->contributing_factors);
        $this->assertEquals('Affected 1000+ users', $result->impact_assessment);
        $this->assertEquals('Added auto-scaling rules', $result->fixes_implemented);
        $this->assertEquals('Jane Smith', $result->approved_by);
        $this->assertEquals('https://example.com/rca/123', $result->report_link);
        $this->assertNotNull($result->approved_at);
    }

    public function test_update_incident_root_cause_analysis_updates_existing_record(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'rca_method' => '5_whys',
            'immediate_cause' => 'Original cause',
            'lessons_learned' => 'Original lessons',
        ]);

        $result = $this->repository->updateIncidentRootCauseAnalysis($rca, [
            'rca_method' => 'fishbone',
            'immediate_cause' => 'Updated cause',
            'lessons_learned' => 'Updated lessons',
        ]);

        $this->assertEquals('fishbone', $result->rca_method);
        $this->assertEquals('Updated cause', $result->immediate_cause);
        $this->assertEquals('Updated lessons', $result->lessons_learned);
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

        $updateData = [
            'ai_incident_id' => $newIncident->id,
            'rca_method' => 'timeline_analysis',
            'immediate_cause' => 'New immediate cause',
            'latent_causes' => 'New latent causes',
            'contributing_factors' => 'New contributing factors',
            'impact_assessment' => 'New impact assessment',
            'fixes_implemented' => 'New fixes',
            'lessons_learned' => 'New lessons learned',
            'recommendations' => 'New recommendations',
            'approved_by' => 'New Approver',
            'approved_at' => now()->addDay(),
            'report_link' => 'https://example.com/new-rca',
        ];

        $result = $this->repository->updateIncidentRootCauseAnalysis($rca, $updateData);

        $this->assertEquals($newIncident->id, $result->ai_incident_id);
        $this->assertEquals('timeline_analysis', $result->rca_method);
        $this->assertEquals('New immediate cause', $result->immediate_cause);
        $this->assertEquals('New latent causes', $result->latent_causes);
        $this->assertEquals('New contributing factors', $result->contributing_factors);
        $this->assertEquals('New impact assessment', $result->impact_assessment);
        $this->assertEquals('New fixes', $result->fixes_implemented);
        $this->assertEquals('New lessons learned', $result->lessons_learned);
        $this->assertEquals('New recommendations', $result->recommendations);
        $this->assertEquals('New Approver', $result->approved_by);
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
        $approvedAt = now()->subDays(2);

        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fault_tree',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'Approver Name',
            'approved_at' => $approvedAt,
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertInstanceOf(\Carbon\Carbon::class, $result->approved_at);
        $this->assertEquals($approvedAt->format('Y-m-d H:i:s'), $result->approved_at->format('Y-m-d H:i:s'));
    }

    public function test_create_incident_root_cause_analysis_with_all_rca_methods(): void
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
                'approved_at' => now(),
            ];

            $result = $this->repository->createIncidentRootCauseAnalysis($data);

            $this->assertEquals($method, $result->rca_method);
        }
    }

    public function test_paginated_rca_loads_ai_incident_relationship(): void
    {
        IncidentRootCauseAnalysis::factory()->count(3)->create();

        $result = $this->repository->getPaginatedIncidentRootCauseAnalyses();

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
            'ai_incident_id' => $incident->id,
            'rca_method' => 'other',
            'immediate_cause' => 'Test cause',
            'latent_causes' => 'Test latent causes',
            'lessons_learned' => 'Test lessons',
            'recommendations' => 'Test recommendations',
            'approved_by' => 'Approver',
            'approved_at' => now(),
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertNull($result->contributing_factors);
        $this->assertNull($result->impact_assessment);
        $this->assertNull($result->fixes_implemented);
        $this->assertNull($result->report_link);
    }

    public function test_create_incident_root_cause_analysis_with_long_text_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $longText = str_repeat('This is a very long description. ', 100);

        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => '5_whys',
            'immediate_cause' => $longText,
            'latent_causes' => $longText,
            'lessons_learned' => $longText,
            'recommendations' => $longText,
            'approved_by' => 'Approver',
            'approved_at' => now(),
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertEquals($longText, $result->immediate_cause);
        $this->assertEquals($longText, $result->latent_causes);
        $this->assertEquals($longText, $result->lessons_learned);
        $this->assertEquals($longText, $result->recommendations);
    }

    public function test_update_can_set_optional_fields_to_null(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'contributing_factors' => 'Some factors',
            'impact_assessment' => 'Some assessment',
            'fixes_implemented' => 'Some fixes',
            'report_link' => 'https://example.com/report',
        ]);

        $result = $this->repository->updateIncidentRootCauseAnalysis($rca, [
            'contributing_factors' => null,
            'impact_assessment' => null,
            'fixes_implemented' => null,
            'report_link' => null,
        ]);

        $this->assertNull($result->contributing_factors);
        $this->assertNull($result->impact_assessment);
        $this->assertNull($result->fixes_implemented);
        $this->assertNull($result->report_link);
    }

    public function test_create_incident_root_cause_analysis_with_complete_fishbone_analysis(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'rca_method' => 'fishbone',
            'immediate_cause' => 'Model deployment failed during production release',
            'latent_causes' => 'Inadequate testing in staging environment, lack of rollback procedures, insufficient monitoring',
            'contributing_factors' => 'Time pressure to meet release deadline, limited DevOps resources',
            'impact_assessment' => 'Service downtime of 2 hours, affected 5000+ users, revenue loss estimated at $10,000',
            'fixes_implemented' => 'Implemented comprehensive staging tests, automated rollback procedures, enhanced monitoring dashboards',
            'lessons_learned' => 'Never skip staging tests under time pressure, always have rollback plan ready, ensure adequate monitoring before releases',
            'recommendations' => 'Mandatory staging approval gate, automated rollback on failure, pre-release monitoring checklist',
            'approved_by' => 'Chief Technology Officer',
            'approved_at' => now(),
            'report_link' => 'https://example.com/rca/fishbone-deployment-failure',
        ];

        $result = $this->repository->createIncidentRootCauseAnalysis($data);

        $this->assertEquals('fishbone', $result->rca_method);
        $this->assertStringContainsString('deployment failed', $result->immediate_cause);
        $this->assertStringContainsString('testing in staging', $result->latent_causes);
        $this->assertStringContainsString('Time pressure', $result->contributing_factors);
        $this->assertStringContainsString('2 hours', $result->impact_assessment);
        $this->assertStringContainsString('rollback procedures', $result->fixes_implemented);
        $this->assertStringContainsString('Never skip', $result->lessons_learned);
        $this->assertStringContainsString('Mandatory staging', $result->recommendations);
    }
}
