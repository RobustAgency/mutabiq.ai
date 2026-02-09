<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\AiModelArtifact;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiModelArtifactObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_model_artifact_create(): void
    {
        $artifact = AiModelArtifact::factory()->create();

        $log = ActivityLog::where('actable_id', $artifact->id)
            ->where('actable_type', AiModelArtifact::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('AiModelArtifact created', $log->description);
    }

    public function test_logs_activity_on_ai_model_artifact_update(): void
    {
        $artifact = AiModelArtifact::factory()->create(['name' => 'Original Name']);

        ActivityLog::truncate();

        $artifact->update(['name' => 'Updated Name']);

        $log = ActivityLog::where('actable_id', $artifact->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('name', $log->changes);
    }

    public function test_logs_activity_on_ai_model_artifact_delete(): void
    {
        $artifact = AiModelArtifact::factory()->create();
        $artifactId = $artifact->id;

        $artifact->delete();

        $log = ActivityLog::where('actable_id', $artifactId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
