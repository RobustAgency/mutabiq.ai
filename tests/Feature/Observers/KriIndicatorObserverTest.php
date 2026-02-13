<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\KriIndicator;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KriIndicatorObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_kri_indicator_create(): void
    {
        $indicator = KriIndicator::factory()->create();

        $log = ActivityLog::where('actable_id', $indicator->id)
            ->where('actable_type', KriIndicator::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('KriIndicator created', $log->description);
    }

    public function test_logs_activity_on_kri_indicator_update(): void
    {
        $indicator = KriIndicator::factory()->create(['status' => 'ACTIVE']);

        ActivityLog::truncate();

        $indicator->update(['status' => 'INACTIVE']);

        $log = ActivityLog::where('actable_id', $indicator->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('KriIndicator updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('ACTIVE', $log->changes['status']['from']);
        $this->assertEquals('INACTIVE', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_kri_indicator_delete(): void
    {
        $indicator = KriIndicator::factory()->create();
        $indicatorId = $indicator->id;

        $indicator->delete();

        $log = ActivityLog::where('actable_id', $indicatorId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('KriIndicator deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $indicator = KriIndicator::factory()->create([
            'name' => 'Original Name',
            'directionality' => 'HIGHER_IS_BETTER',
            'unit' => 'PERCENTAGE',
            'frequency' => 'DAILY',
            'status' => 'ACTIVE',
        ]);

        ActivityLog::truncate();

        $indicator->update([
            'name' => 'Updated Name',
            'directionality' => 'LOWER_IS_BETTER',
            'unit' => 'COUNT',
            'frequency' => 'WEEKLY',
            'status' => 'INACTIVE',
        ]);

        $log = ActivityLog::where('actable_id', $indicator->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertArrayHasKey('directionality', $log->changes);
        $this->assertArrayHasKey('unit', $log->changes);
        $this->assertArrayHasKey('frequency', $log->changes);
        $this->assertArrayHasKey('status', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $indicator = KriIndicator::factory()->create();

        $log = ActivityLog::where('actable_id', $indicator->id)
            ->where('actable_type', KriIndicator::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($indicator->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $indicator = KriIndicator::factory()->create();

        $log = ActivityLog::where('actable_id', $indicator->id)
            ->where('actable_type', KriIndicator::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_threshold_changes(): void
    {
        $indicator = KriIndicator::factory()->create([
            'threshold_warning' => 10,
            'threshold_critical' => 20,
        ]);

        ActivityLog::truncate();

        $indicator->update([
            'threshold_warning' => 15,
            'threshold_critical' => 30,
        ]);

        $log = ActivityLog::where('actable_id', $indicator->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('threshold_warning', $log->changes);
        $this->assertArrayHasKey('threshold_critical', $log->changes);
    }

    public function test_tracks_alert_routing_array_changes(): void
    {
        $indicator = KriIndicator::factory()->create(['alert_routing' => ['team1@example.com']]);

        ActivityLog::truncate();

        $indicator->update(['alert_routing' => ['team1@example.com', 'team2@example.com']]);

        $log = ActivityLog::where('actable_id', $indicator->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('alert_routing', $log->changes);
        $this->assertEquals(['team1@example.com'], $log->changes['alert_routing']['from']);
        $this->assertEquals(['team1@example.com', 'team2@example.com'], $log->changes['alert_routing']['to']);
    }

    public function test_tracks_definition_and_notes_changes(): void
    {
        $indicator = KriIndicator::factory()->create([
            'definition' => 'Original definition',
            'notes' => 'Original notes',
        ]);

        ActivityLog::truncate();

        $indicator->update([
            'definition' => 'Updated definition',
            'notes' => 'Updated notes',
        ]);

        $log = ActivityLog::where('actable_id', $indicator->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('definition', $log->changes);
        $this->assertArrayHasKey('notes', $log->changes);
    }
}
