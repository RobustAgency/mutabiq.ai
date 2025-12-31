<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\AiIncident;
use App\Models\Organization;
use App\Models\IncidentAlert;
use App\Enums\IncidentAlert\AlertSeverity;
use App\Enums\IncidentAlert\AlertSourceType;
use App\Repositories\IncidentAlertRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        IncidentAlert::factory()->count(25)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getFilteredIncidentAlerts(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_create_incident_alert_creates_new_record(): void
    {
        $incident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'source_type' => AlertSourceType::KRI_THRESHOLD->value,
            'alert_sensitivity' => AlertSeverity::MEDIUM->value,
            'context' => 'Test alert context',
            'first_seen_at' => now()->subHour(),
        ];

        $result = $this->repository->createIncidentAlert($data);

        $this->assertInstanceOf(IncidentAlert::class, $result);
        $this->assertEquals($incident->id, $result->ai_incident_id);
        $this->assertEquals(AlertSourceType::KRI_THRESHOLD->value, $result->source_type);
        $this->assertEquals(AlertSeverity::MEDIUM->value, $result->alert_sensitivity);
        $this->assertDatabaseHas('incident_alerts', [
            'id' => $result->id,
            'ai_incident_id' => $incident->id,
            'source_type' => AlertSourceType::KRI_THRESHOLD->value,
        ]);
    }

    public function test_create_incident_alert_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'source_type' => AlertSourceType::MONITORING_RULE->value,
            'data_source_id' => null,
            'source_ref' => 'RULE-1234',
            'alert_sensitivity' => AlertSeverity::HIGH->value,
            'context' => 'Test context for alert',
            'first_seen_at' => now()->subHours(2),
            'last_seen_at' => now()->subHour(),
            'evidence_link' => 'https://example.com/evidence/123',
            'auto_promote_incident' => true,
        ];

        $result = $this->repository->createIncidentAlert($data);

        $this->assertEquals(AlertSourceType::MONITORING_RULE->value, $result->source_type);
        $this->assertEquals('RULE-1234', $result->source_ref);
        $this->assertEquals(AlertSeverity::HIGH->value, $result->alert_sensitivity);
        $this->assertEquals('Test context for alert', $result->context);
        $this->assertEquals('https://example.com/evidence/123', $result->evidence_link);
        $this->assertTrue($result->auto_promote_incident);
        $this->assertNotNull($result->first_seen_at);
        $this->assertNotNull($result->last_seen_at);
    }

    public function test_update_incident_alert_updates_existing_record(): void
    {
        $alert = IncidentAlert::factory()->create([
            'organization_id' => $this->organization->id,
            'source_type' => AlertSourceType::KRI_THRESHOLD->value,
            'source_ref' => 'OLD-REF',
        ]);

        $result = $this->repository->updateIncidentAlert($alert, [
            'source_type' => AlertSourceType::MONITORING_RULE->value,
            'source_ref' => 'NEW-REF',
        ]);

        $this->assertEquals(AlertSourceType::MONITORING_RULE->value, $result->source_type);
        $this->assertEquals('NEW-REF', $result->source_ref);
        $this->assertDatabaseHas('incident_alerts', [
            'id' => $alert->id,
            'source_type' => AlertSourceType::MONITORING_RULE->value,
            'source_ref' => 'NEW-REF',
        ]);
    }

    public function test_update_incident_alert_can_update_all_fields(): void
    {
        $alert = IncidentAlert::factory()->create(['organization_id' => $this->organization->id]);
        $newIncident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = [
            'ai_incident_id' => $newIncident->id,
            'source_type' => AlertSourceType::MANUAL_REPORT->value,
            'source_ref' => 'TICKET-9999',
            'alert_sensitivity' => AlertSeverity::CRITICAL->value,
            'context' => 'Updated context',
            'last_seen_at' => now(),
            'evidence_link' => 'https://example.com/new-evidence',
            'auto_promote_incident' => false,
        ];

        $result = $this->repository->updateIncidentAlert($alert, $updateData);

        $this->assertEquals($newIncident->id, $result->ai_incident_id);
        $this->assertEquals(AlertSourceType::MANUAL_REPORT->value, $result->source_type);
        $this->assertEquals('TICKET-9999', $result->source_ref);
        $this->assertEquals(AlertSeverity::CRITICAL->value, $result->alert_sensitivity);
        $this->assertEquals('Updated context', $result->context);
        $this->assertEquals('https://example.com/new-evidence', $result->evidence_link);
        $this->assertFalse($result->auto_promote_incident);
    }

    public function test_delete_incident_alert_removes_record(): void
    {
        $alert = IncidentAlert::factory()->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->deleteIncidentAlert($alert);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('incident_alerts', ['id' => $alert->id]);
    }

    public function test_get_incident_alert_by_id_returns_alert(): void
    {
        $alert = IncidentAlert::factory()->create(['organization_id' => $this->organization->id]);
        $result = $this->repository->getIncidentAlertById($alert);

        $this->assertInstanceOf(IncidentAlert::class, $result);
        $this->assertEquals($alert->id, $result->id);
    }

    public function test_create_incident_alert_with_datetime_fields(): void
    {
        $incident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);
        $firstSeen = now()->subHours(3);
        $lastSeen = now()->subHour();

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'source_type' => AlertSourceType::AUTOMATED_SCAN->value,
            'alert_sensitivity' => AlertSeverity::LOW->value,
            'context' => 'Automated scan context',
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
        $incident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);
        $sourceTypes = [
            AlertSourceType::MONITORING_RULE->value,
            AlertSourceType::KRI_THRESHOLD->value,
            AlertSourceType::MANUAL_REPORT->value,
            AlertSourceType::AUTOMATED_SCAN->value,
            AlertSourceType::USER_COMPLAINT->value,
            AlertSourceType::EXTERNAL_REPORT->value,
        ];

        foreach ($sourceTypes as $sourceType) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'source_type' => $sourceType,
                'alert_sensitivity' => AlertSeverity::MEDIUM->value,
                'context' => "Alert context for {$sourceType}",
                'first_seen_at' => now(),
            ];

            $result = $this->repository->createIncidentAlert($data);

            $this->assertEquals($sourceType, $result->source_type);
        }
    }

    public function test_paginated_alerts_loads_ai_incident_relationship(): void
    {
        IncidentAlert::factory()->count(3)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getFilteredIncidentAlerts(['organization_id' => $this->organization->id]);

        $this->assertTrue($result->items()[0]->relationLoaded('aiIncident'));
    }

    public function test_get_by_id_loads_ai_incident_relationship(): void
    {
        $alert = IncidentAlert::factory()->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getIncidentAlertById($alert);

        $this->assertTrue($result->relationLoaded('aiIncident'));
    }

    public function test_create_incident_alert_with_all_severities(): void
    {
        $incident = AiIncident::factory()->create(['organization_id' => $this->organization->id]);
        $severities = [
            AlertSeverity::LOW->value,
            AlertSeverity::MEDIUM->value,
            AlertSeverity::HIGH->value,
            AlertSeverity::CRITICAL->value,
        ];

        foreach ($severities as $severity) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'source_type' => AlertSourceType::MONITORING_RULE->value,
                'alert_sensitivity' => $severity,
                'context' => "Alert with {$severity} severity",
                'first_seen_at' => now(),
            ];

            $result = $this->repository->createIncidentAlert($data);

            $this->assertEquals($severity, $result->alert_sensitivity);
        }
    }
}
