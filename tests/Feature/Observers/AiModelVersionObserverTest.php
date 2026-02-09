<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\AiModelVersion;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiModelVersionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_model_version_create(): void
    {
        $version = AiModelVersion::factory()->create();

        $log = ActivityLog::where('actable_id', $version->id)
            ->where('actable_type', AiModelVersion::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('AiModelVersion created', $log->description);
    }

    public function test_logs_activity_on_ai_model_version_update(): void
    {
        $version = AiModelVersion::factory()->create(['deployment_status' => 'staging']);

        ActivityLog::truncate();

        $version->update(['deployment_status' => 'production']);

        $log = ActivityLog::where('actable_id', $version->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('deployment_status', $log->changes);
        $this->assertEquals('staging', $log->changes['deployment_status']['from']);
        $this->assertEquals('production', $log->changes['deployment_status']['to']);
    }

    public function test_logs_activity_on_ai_model_version_delete(): void
    {
        $version = AiModelVersion::factory()->create();
        $versionId = $version->id;

        $version->delete();

        $log = ActivityLog::where('actable_id', $versionId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
