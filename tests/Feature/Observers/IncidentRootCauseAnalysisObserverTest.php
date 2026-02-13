<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use App\Models\IncidentRootCauseAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IncidentRootCauseAnalysisObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_incident_root_cause_analysis_create(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $log = ActivityLog::where('actable_id', $rca->id)
            ->where('actable_type', IncidentRootCauseAnalysis::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('IncidentRootCauseAnalysis created', $log->description);
    }

    public function test_logs_activity_on_incident_root_cause_analysis_update(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create(['rca_method' => 'FISHBONE']);

        ActivityLog::truncate();

        $rca->update(['rca_method' => 'FAULT_TREE']);

        $log = ActivityLog::where('actable_id', $rca->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('IncidentRootCauseAnalysis updated', $log->description);
        $this->assertArrayHasKey('rca_method', $log->changes);
        $this->assertEquals('FISHBONE', $log->changes['rca_method']['from']);
        $this->assertEquals('FAULT_TREE', $log->changes['rca_method']['to']);
    }

    public function test_logs_activity_on_incident_root_cause_analysis_delete(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();
        $rcaId = $rca->id;

        $rca->delete();

        $log = ActivityLog::where('actable_id', $rcaId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('IncidentRootCauseAnalysis deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'rca_method' => 'FISHBONE',
            'analysis_date' => now()->format('Y-m-d'),
            'immediate_cause' => 'Configuration error',
            'lead_analyst' => 'analyst@example.com',
        ]);

        ActivityLog::truncate();

        $rca->update([
            'rca_method' => 'FAULT_TREE',
            'analysis_date' => now()->subDays(1)->format('Y-m-d'),
            'immediate_cause' => 'Deployment mistake',
            'lead_analyst' => 'new_analyst@example.com',
        ]);

        $log = ActivityLog::where('actable_id', $rca->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('rca_method', $log->changes);
        $this->assertArrayHasKey('analysis_date', $log->changes);
        $this->assertArrayHasKey('immediate_cause', $log->changes);
        $this->assertArrayHasKey('lead_analyst', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $log = ActivityLog::where('actable_id', $rca->id)
            ->where('actable_type', IncidentRootCauseAnalysis::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($rca->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create();

        $log = ActivityLog::where('actable_id', $rca->id)
            ->where('actable_type', IncidentRootCauseAnalysis::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_cause_analysis_changes(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'immediate_cause' => 'Configuration error',
            'root_causes' => ['Inadequate testing', 'Missing validation'],
            'contributing_factors' => ['Staff overload'],
        ]);

        ActivityLog::truncate();

        $rca->update([
            'immediate_cause' => 'Deployment mistake',
            'root_causes' => ['Insufficient code review', 'Missing checklist'],
            'contributing_factors' => ['Staff shortage', 'Time pressure'],
        ]);

        $log = ActivityLog::where('actable_id', $rca->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('immediate_cause', $log->changes);
        $this->assertArrayHasKey('root_causes', $log->changes);
        $this->assertArrayHasKey('contributing_factors', $log->changes);
    }

    public function test_tracks_control_failures_and_recommendations(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'control_failures' => ['Code review not enforced'],
            'recommendations' => ['Implement mandatory code review'],
        ]);

        ActivityLog::truncate();

        $rca->update([
            'control_failures' => ['Code review not enforced', 'No automated testing'],
            'recommendations' => ['Implement mandatory code review', 'Add automated tests'],
        ]);

        $log = ActivityLog::where('actable_id', $rca->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('control_failures', $log->changes);
        $this->assertArrayHasKey('recommendations', $log->changes);
    }

    public function test_tracks_approval_changes(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'approved_at' => null,
            'review_committee' => null,
        ]);

        ActivityLog::truncate();

        $rca->update([
            'approved_at' => now(),
            'review_committee' => 'Risk Management Committee',
        ]);

        $log = ActivityLog::where('actable_id', $rca->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('approved_at', $log->changes);
        $this->assertArrayHasKey('review_committee', $log->changes);
    }

    public function test_tracks_report_link_changes(): void
    {
        $rca = IncidentRootCauseAnalysis::factory()->create([
            'report_link' => 'https://example.com/rca/1',
        ]);

        ActivityLog::truncate();

        $rca->update([
            'report_link' => 'https://example.com/rca/2',
        ]);

        $log = ActivityLog::where('actable_id', $rca->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('report_link', $log->changes);
    }
}
