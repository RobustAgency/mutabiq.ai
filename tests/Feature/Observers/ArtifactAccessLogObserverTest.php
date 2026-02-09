<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\ArtifactAccessLog;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArtifactAccessLogObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_artifact_access_log_create(): void
    {
        $accessLog = ArtifactAccessLog::factory()->create();

        $log = ActivityLog::where('actable_id', $accessLog->id)
            ->where('actable_type', ArtifactAccessLog::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('ArtifactAccessLog created', $log->description);
    }

    public function test_logs_activity_on_artifact_access_log_update(): void
    {
        $accessLog = ArtifactAccessLog::factory()->create(['reason' => 'Initial reason']);

        ActivityLog::truncate();

        $accessLog->update(['reason' => 'Updated reason']);

        $log = ActivityLog::where('actable_id', $accessLog->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('reason', $log->changes);
    }

    public function test_logs_activity_on_artifact_access_log_delete(): void
    {
        $accessLog = ArtifactAccessLog::factory()->create();
        $accessLogId = $accessLog->id;

        $accessLog->delete();

        $log = ActivityLog::where('actable_id', $accessLogId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
