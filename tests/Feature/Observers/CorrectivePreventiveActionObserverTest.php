<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use App\Models\CorrectivePreventiveAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CorrectivePreventiveActionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_corrective_preventive_action_create(): void
    {
        $action = CorrectivePreventiveAction::factory()->create();

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('actable_type', CorrectivePreventiveAction::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('CorrectivePreventiveAction created', $log->description);
    }

    public function test_logs_activity_on_corrective_preventive_action_update(): void
    {
        $action = CorrectivePreventiveAction::factory()->create(['status' => 'OPEN']);

        ActivityLog::truncate();

        $action->update(['status' => 'IN_PROGRESS']);

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('CorrectivePreventiveAction updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('OPEN', $log->changes['status']['from']);
        $this->assertEquals('IN_PROGRESS', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_corrective_preventive_action_delete(): void
    {
        $action = CorrectivePreventiveAction::factory()->create();
        $actionId = $action->id;

        $action->delete();

        $log = ActivityLog::where('actable_id', $actionId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('CorrectivePreventiveAction deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $action = CorrectivePreventiveAction::factory()->create([
            'title' => 'Original Title',
            'priority' => 'HIGH',
            'status' => 'OPEN',
            'capa_type' => 'CORRECTIVE',
        ]);

        ActivityLog::truncate();

        $action->update([
            'title' => 'Updated Title',
            'priority' => 'CRITICAL',
            'status' => 'CLOSED',
            'capa_type' => 'PREVENTIVE',
        ]);

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('title', $log->changes);
        $this->assertArrayHasKey('priority', $log->changes);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertArrayHasKey('capa_type', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $action = CorrectivePreventiveAction::factory()->create();

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('actable_type', CorrectivePreventiveAction::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($action->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $action = CorrectivePreventiveAction::factory()->create();

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('actable_type', CorrectivePreventiveAction::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_due_date_changes(): void
    {
        $action = CorrectivePreventiveAction::factory()->create([
            'due_date' => now()->format('Y-m-d'),
        ]);

        ActivityLog::truncate();

        $action->update(['due_date' => now()->addMonth()->format('Y-m-d')]);

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('due_date', $log->changes);
    }
}
