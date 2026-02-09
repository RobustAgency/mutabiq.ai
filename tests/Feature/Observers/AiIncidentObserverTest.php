<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\AiIncident;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiIncidentObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_incident_create(): void
    {
        $incident = AiIncident::factory()->create();

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('actable_type', AiIncident::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals($incident->organization_id, $log->organization_id);
        $this->assertEquals('AiIncident created', $log->description);
    }

    public function test_logs_activity_on_ai_incident_update(): void
    {
        $incident = AiIncident::factory()->create(['status' => 'DRAFT']);

        ActivityLog::truncate();

        $incident->update(['status' => 'RESOLVED']);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('AiIncident updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('DRAFT', $log->changes['status']['from']);
        $this->assertEquals('RESOLVED', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_ai_incident_delete(): void
    {
        $incident = AiIncident::factory()->create();
        $incidentId = $incident->id;

        $incident->delete();

        $log = ActivityLog::where('actable_id', $incidentId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('AiIncident deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $incident = AiIncident::factory()->create([
            'status' => 'DRAFT',
            'severity' => 'MEDIUM',
            'title' => 'Original Title',
        ]);

        ActivityLog::truncate();

        $incident->update([
            'status' => 'RESOLVED',
            'severity' => 'HIGH',
            'title' => 'Updated Title',
        ]);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE->value)
            ->first();

        $this->assertArrayHasKey('status', $log->changes);
        $this->assertArrayHasKey('severity', $log->changes);
        $this->assertArrayHasKey('title', $log->changes);
    }

    public function test_captures_ip_address_and_user_agent(): void
    {
        $incident = AiIncident::factory()->create();

        $log = ActivityLog::where('actable_id', $incident->id)->first();

        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_does_not_log_untracked_field_changes(): void
    {
        $incident = AiIncident::factory()->create([
            'summary' => 'Original summary',
        ]);

        ActivityLog::truncate();

        $incident->update(['summary' => 'Updated summary']);

        $log = ActivityLog::where('actable_id', $incident->id)
            ->where('action', ActivityAction::UPDATE->value)
            ->first();

        $this->assertNull($log);
    }
}
