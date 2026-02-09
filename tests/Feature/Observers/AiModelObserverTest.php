<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\AiModel;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiModelObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_model_create(): void
    {
        $aiModel = AiModel::factory()->create();

        $log = ActivityLog::where('actable_id', $aiModel->id)
            ->where('actable_type', AiModel::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('AiModel created', $log->description);
    }

    public function test_logs_activity_on_ai_model_update(): void
    {
        $aiModel = AiModel::factory()->create(['name' => 'Original Name']);

        ActivityLog::truncate();

        $aiModel->update(['name' => 'Updated Name']);

        $log = ActivityLog::where('actable_id', $aiModel->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertEquals('Original Name', $log->changes['name']['from']);
        $this->assertEquals('Updated Name', $log->changes['name']['to']);
    }

    public function test_logs_activity_on_ai_model_delete(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelId = $aiModel->id;

        $aiModel->delete();

        $log = ActivityLog::where('actable_id', $aiModelId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
