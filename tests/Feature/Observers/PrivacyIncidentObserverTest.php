<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\PrivacyIncident;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrivacyIncidentObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_privacy_incident_create(): void
    {
        $incident = PrivacyIncident::factory()->create();

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('actable_type', PrivacyIncident::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('PrivacyIncident created', $log->description);
    }

    public function test_logs_activity_on_privacy_incident_update(): void
    {
        $incident = PrivacyIncident::factory()->create(['risk_level' => 'LOW']);

        ActivityLog::truncate();

        $incident->update(['risk_level' => 'HIGH']);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('PrivacyIncident updated', $log->description);
        $this->assertArrayHasKey('risk_level', $log->changes);
        $this->assertEquals('LOW', $log->changes['risk_level']['from']);
        $this->assertEquals('HIGH', $log->changes['risk_level']['to']);
    }

    public function test_logs_activity_on_privacy_incident_delete(): void
    {
        $incident = PrivacyIncident::factory()->create();
        $incidentId = $incident->id;

        $incident->delete();

        $log = ActivityLog::where('actable_id', $incidentId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('PrivacyIncident deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $incident = PrivacyIncident::factory()->create([
            'incident_title' => 'Original Title',
            'incident_type' => 'UNAUTHORIZED_ACCESS',
            'risk_level' => 'LOW',
            'is_breach' => false,
            'notification_status' => 'NOT_REQUIRED',
        ]);

        ActivityLog::truncate();

        $incident->update([
            'incident_title' => 'Updated Title',
            'incident_type' => 'DATA_LOSS',
            'risk_level' => 'HIGH',
            'is_breach' => true,
            'notification_status' => 'PENDING',
        ]);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('incident_title', $log->changes);
        $this->assertArrayHasKey('incident_type', $log->changes);
        $this->assertArrayHasKey('risk_level', $log->changes);
        $this->assertArrayHasKey('is_breach', $log->changes);
        $this->assertArrayHasKey('notification_status', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $incident = PrivacyIncident::factory()->create();

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('actable_type', PrivacyIncident::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($incident->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $incident = PrivacyIncident::factory()->create();

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('actable_type', PrivacyIncident::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_breach_assessment_changes(): void
    {
        $incident = PrivacyIncident::factory()->create([
            'is_breach' => false,
            'breach_criteria_met' => false,
        ]);

        ActivityLog::truncate();

        $incident->update([
            'is_breach' => true,
            'breach_criteria_met' => true,
        ]);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('is_breach', $log->changes);
        $this->assertArrayHasKey('breach_criteria_met', $log->changes);
    }

    public function test_tracks_timeline_changes(): void
    {
        $incident = PrivacyIncident::factory()->create([
            'detected_date' => now()->subDays(5),
            'occurred_date' => now()->subDays(10),
            'notification_deadline' => now()->addDays(3),
        ]);

        ActivityLog::truncate();

        $incident->update([
            'detected_date' => now()->subDays(2),
            'notification_deadline' => now()->addDays(1),
        ]);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('detected_date', $log->changes);
        $this->assertArrayHasKey('notification_deadline', $log->changes);
    }

    public function test_tracks_notification_changes(): void
    {
        $incident = PrivacyIncident::factory()->create([
            'notification_required' => false,
            'notification_status' => 'NOT_REQUIRED',
            'authority_notified' => false,
        ]);

        ActivityLog::truncate();

        $incident->update([
            'notification_required' => true,
            'notification_status' => 'DELIVERED',
            'authority_notified' => true,
            'authority_notification_date' => now(),
        ]);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('notification_required', $log->changes);
        $this->assertArrayHasKey('notification_status', $log->changes);
        $this->assertArrayHasKey('authority_notified', $log->changes);
        $this->assertArrayHasKey('authority_notification_date', $log->changes);
    }

    public function test_tracks_data_impact_changes(): void
    {
        $incident = PrivacyIncident::factory()->create([
            'data_compromised' => false,
            'data_categories_affected' => ['NAME'],
            'estimated_affected_subjects' => 0,
        ]);

        ActivityLog::truncate();

        $incident->update([
            'data_compromised' => true,
            'data_categories_affected' => ['NAME', 'EMAIL', 'PHONE'],
            'estimated_affected_subjects' => 1000,
        ]);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('data_compromised', $log->changes);
        $this->assertArrayHasKey('data_categories_affected', $log->changes);
        $this->assertArrayHasKey('estimated_affected_subjects', $log->changes);
    }

    public function test_tracks_remediation_changes(): void
    {
        $incident = PrivacyIncident::factory()->create([
            'immediate_actions' => ['Contain breach'],
            'mitigation_measures' => ['Reset passwords'],
        ]);

        ActivityLog::truncate();

        $incident->update([
            'immediate_actions' => ['Contain breach', 'Secure systems'],
            'mitigation_measures' => ['Reset passwords', 'Implement 2FA'],
            'lessons_learned' => 'Improve access controls',
        ]);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('immediate_actions', $log->changes);
        $this->assertArrayHasKey('mitigation_measures', $log->changes);
        $this->assertArrayHasKey('lessons_learned', $log->changes);
    }
}
