<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\IncidentAlert;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IncidentAlertObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_incident_alert_create(): void
    {
        $alert = IncidentAlert::factory()->create();

        $log = ActivityLog::where('actable_id', $alert->id)
            ->where('actable_type', IncidentAlert::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('IncidentAlert created', $log->description);
    }

    public function test_logs_activity_on_incident_alert_update(): void
    {
        $alert = IncidentAlert::factory()->create(['alert_sensitivity' => 'LOW']);

        ActivityLog::truncate();

        $alert->update(['alert_sensitivity' => 'HIGH']);

        $log = ActivityLog::where('actable_id', $alert->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('IncidentAlert updated', $log->description);
        $this->assertArrayHasKey('alert_sensitivity', $log->changes);
        $this->assertEquals('LOW', $log->changes['alert_sensitivity']['from']);
        $this->assertEquals('HIGH', $log->changes['alert_sensitivity']['to']);
    }

    public function test_logs_activity_on_incident_alert_delete(): void
    {
        $alert = IncidentAlert::factory()->create();
        $alertId = $alert->id;

        $alert->delete();

        $log = ActivityLog::where('actable_id', $alertId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('IncidentAlert deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $alert = IncidentAlert::factory()->create([
            'source_type' => 'MONITORING_SYSTEM',
            'alert_sensitivity' => 'LOW',
            'auto_promote_incident' => false,
        ]);

        ActivityLog::truncate();

        $alert->update([
            'source_type' => 'MANUAL_REPORT',
            'alert_sensitivity' => 'CRITICAL',
            'auto_promote_incident' => true,
        ]);

        $log = ActivityLog::where('actable_id', $alert->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('source_type', $log->changes);
        $this->assertArrayHasKey('alert_sensitivity', $log->changes);
        $this->assertArrayHasKey('auto_promote_incident', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $alert = IncidentAlert::factory()->create();

        $log = ActivityLog::where('actable_id', $alert->id)
            ->where('actable_type', IncidentAlert::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($alert->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $alert = IncidentAlert::factory()->create();

        $log = ActivityLog::where('actable_id', $alert->id)
            ->where('actable_type', IncidentAlert::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_timing_changes(): void
    {
        $alert = IncidentAlert::factory()->create([
            'first_seen_at' => now()->subHours(2),
            'last_seen_at' => now()->subHours(1),
        ]);

        ActivityLog::truncate();

        $alert->update([
            'last_seen_at' => now(),
        ]);

        $log = ActivityLog::where('actable_id', $alert->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('last_seen_at', $log->changes);
    }

    public function test_tracks_source_reference_changes(): void
    {
        $alert = IncidentAlert::factory()->create([
            'source_ref' => 'alert-001',
            'evidence_link' => 'https://example.com/evidence/1',
        ]);

        ActivityLog::truncate();

        $alert->update([
            'source_ref' => 'alert-002',
            'evidence_link' => 'https://example.com/evidence/2',
        ]);

        $log = ActivityLog::where('actable_id', $alert->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('source_ref', $log->changes);
        $this->assertArrayHasKey('evidence_link', $log->changes);
    }
}
