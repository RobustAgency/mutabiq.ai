<?php

namespace Tests\Feature\Repositories;

use App\Models\AiIncident;
use App\Models\IncidentAlert;
use App\Models\Organization;
use App\Repositories\IncidentAlertRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentAlertRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected IncidentAlertRepository $repository;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(IncidentAlertRepository::class);
        $this->organization = Organization::factory()->create();
    }

    public function test_get_paginated_incident_alerts_returns_paginated_results(): void
    {
        IncidentAlert::factory()->count(25)->create();

        $result = $this->repository->getFilteredIncidentAlerts(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_create_incident_alert_creates_new_record(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'source_type' => 'kri',
            'first_seen_at' => now()->subHour(),
        ];

        $result = $this->repository->createIncidentAlert($data);

        $this->assertInstanceOf(IncidentAlert::class, $result);
        $this->assertEquals($incident->id, $result->ai_incident_id);
        $this->assertEquals('kri', $result->source_type);
        $this->assertDatabaseHas('incident_alerts', [
            'id' => $result->id,
            'ai_incident_id' => $incident->id,
            'source_type' => 'kri',
        ]);
    }

    public function test_create_incident_alert_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'source_type' => 'monitoring_rule',
            'source_ref' => 'RULE-1234',
            'rule_version' => 'v2.1.0',
            'context' => 'Test context for alert',
            'first_seen_at' => now()->subHours(2),
            'last_seen_at' => now()->subHour(),
            'evidence_link' => 'https://example.com/evidence/123',
        ];

        $result = $this->repository->createIncidentAlert($data);

        $this->assertEquals('monitoring_rule', $result->source_type);
        $this->assertEquals('RULE-1234', $result->source_ref);
        $this->assertEquals('v2.1.0', $result->rule_version);
        $this->assertEquals('Test context for alert', $result->context);
        $this->assertEquals('https://example.com/evidence/123', $result->evidence_link);
        $this->assertNotNull($result->first_seen_at);
        $this->assertNotNull($result->last_seen_at);
    }

    public function test_update_incident_alert_updates_existing_record(): void
    {
        $alert = IncidentAlert::factory()->create([
            'source_type' => 'kri',
            'source_ref' => 'OLD-REF',
        ]);

        $result = $this->repository->updateIncidentAlert($alert, [
            'source_type' => 'monitoring_rule',
            'source_ref' => 'NEW-REF',
        ]);

        $this->assertEquals('monitoring_rule', $result->source_type);
        $this->assertEquals('NEW-REF', $result->source_ref);
        $this->assertDatabaseHas('incident_alerts', [
            'id' => $alert->id,
            'source_type' => 'monitoring_rule',
            'source_ref' => 'NEW-REF',
        ]);
    }

    public function test_update_incident_alert_can_update_all_fields(): void
    {
        $alert = IncidentAlert::factory()->create();
        $newIncident = AiIncident::factory()->create();

        $updateData = [
            'ai_incident_id' => $newIncident->id,
            'source_type' => 'human_report',
            'source_ref' => 'TICKET-9999',
            'rule_version' => 'v3.0.0',
            'context' => 'Updated context',
            'last_seen_at' => now(),
            'evidence_link' => 'https://example.com/new-evidence',
        ];

        $result = $this->repository->updateIncidentAlert($alert, $updateData);

        $this->assertEquals($newIncident->id, $result->ai_incident_id);
        $this->assertEquals('human_report', $result->source_type);
        $this->assertEquals('TICKET-9999', $result->source_ref);
        $this->assertEquals('v3.0.0', $result->rule_version);
        $this->assertEquals('Updated context', $result->context);
        $this->assertEquals('https://example.com/new-evidence', $result->evidence_link);
    }

    public function test_delete_incident_alert_removes_record(): void
    {
        $alert = IncidentAlert::factory()->create();

        $result = $this->repository->deleteIncidentAlert($alert);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('incident_alerts', ['id' => $alert->id]);
    }

    public function test_get_incident_alert_by_id_returns_alert(): void
    {
        $alert = IncidentAlert::factory()->create();
        $result = $this->repository->getIncidentAlertById($alert);

        $this->assertInstanceOf(IncidentAlert::class, $result);
        $this->assertEquals($alert->id, $result->id);
    }

    public function test_create_incident_alert_with_datetime_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $firstSeen = now()->subHours(3);
        $lastSeen = now()->subHour();

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'source_type' => 'security_tool',
            'first_seen_at' => $firstSeen,
            'last_seen_at' => $lastSeen,
        ];

        $result = $this->repository->createIncidentAlert($data);

        $this->assertInstanceOf(\Carbon\Carbon::class, $result->first_seen_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result->last_seen_at);
        $this->assertEquals($firstSeen->format('Y-m-d H:i:s'), $result->first_seen_at->format('Y-m-d H:i:s'));
        $this->assertEquals($lastSeen->format('Y-m-d H:i:s'), $result->last_seen_at->format('Y-m-d H:i:s'));
    }

    public function test_create_incident_alert_with_all_source_types(): void
    {
        $incident = AiIncident::factory()->create();
        $sourceTypes = ['kri', 'monitoring_rule', 'human_report', 'vendor_notice', 'security_tool', 'other'];

        foreach ($sourceTypes as $sourceType) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'source_type' => $sourceType,
                'first_seen_at' => now(),
            ];

            $result = $this->repository->createIncidentAlert($data);

            $this->assertEquals($sourceType, $result->source_type);
        }
    }

    public function test_paginated_alerts_loads_ai_incident_relationship(): void
    {
        IncidentAlert::factory()->count(3)->create();

        $result = $this->repository->getFilteredIncidentAlerts();

        $this->assertTrue($result->items()[0]->relationLoaded('aiIncident'));
    }

    public function test_get_by_id_loads_ai_incident_relationship(): void
    {
        $alert = IncidentAlert::factory()->create();

        $result = $this->repository->getIncidentAlertById($alert);

        $this->assertTrue($result->relationLoaded('aiIncident'));
    }
}
