<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\IncidentAction;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IncidentActionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_incident_action_create(): void
    {
        $action = IncidentAction::factory()->create();

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('actable_type', IncidentAction::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('IncidentAction created', $log->description);
    }

    public function test_logs_activity_on_incident_action_update(): void
    {
        $action = IncidentAction::factory()->create(['execution_status' => 'PENDING']);

        ActivityLog::truncate();

        $action->update(['execution_status' => 'IN_PROGRESS']);

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('IncidentAction updated', $log->description);
        $this->assertArrayHasKey('execution_status', $log->changes);
        $this->assertEquals('PENDING', $log->changes['execution_status']['from']);
        $this->assertEquals('IN_PROGRESS', $log->changes['execution_status']['to']);
    }

    public function test_logs_activity_on_incident_action_delete(): void
    {
        $action = IncidentAction::factory()->create();
        $actionId = $action->id;

        $action->delete();

        $log = ActivityLog::where('actable_id', $actionId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('IncidentAction deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $action = IncidentAction::factory()->create([
            'action_type' => 'CONTAINMENT',
            'execution_status' => 'PENDING',
            'individual_name' => 'Original Name',
            'approval_required' => false,
        ]);

        ActivityLog::truncate();

        $action->update([
            'action_type' => 'REMEDIATION',
            'execution_status' => 'COMPLETED',
            'individual_name' => 'Updated Name',
            'approval_required' => true,
        ]);

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('action_type', $log->changes);
        $this->assertArrayHasKey('execution_status', $log->changes);
        $this->assertArrayHasKey('individual_name', $log->changes);
        $this->assertArrayHasKey('approval_required', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $action = IncidentAction::factory()->create();

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('actable_type', IncidentAction::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($action->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $action = IncidentAction::factory()->create();

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('actable_type', IncidentAction::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_execution_timing_changes(): void
    {
        $action = IncidentAction::factory()->create([
            'started_at' => now()->subHours(2),
            'completed_at' => null,
            'actual_duration' => null,
        ]);

        ActivityLog::truncate();

        $action->update([
            'completed_at' => now(),
            'actual_duration' => 120,
        ]);

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('completed_at', $log->changes);
        $this->assertArrayHasKey('actual_duration', $log->changes);
    }

    public function test_tracks_validation_result_changes(): void
    {
        $action = IncidentAction::factory()->create([
            'validation_result' => 'PENDING',
            'validation_notes' => 'Awaiting review',
        ]);

        ActivityLog::truncate();

        $action->update([
            'validation_result' => 'APPROVED',
            'validation_notes' => 'Action validated successfully',
        ]);

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('validation_result', $log->changes);
        $this->assertArrayHasKey('validation_notes', $log->changes);
    }

    public function test_tracks_approval_requirement_changes(): void
    {
        $action = IncidentAction::factory()->create(['approval_required' => false]);

        ActivityLog::truncate();

        $action->update(['approval_required' => true]);

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('approval_required', $log->changes);
        $this->assertFalse($log->changes['approval_required']['from']);
        $this->assertTrue($log->changes['approval_required']['to']);
    }
}
