<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\AiModelUseCase;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiModelUseCaseObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_model_use_case_create(): void
    {
        $modelUseCase = AiModelUseCase::factory()->create();

        $log = ActivityLog::where('actable_id', $modelUseCase->id)
            ->where('actable_type', AiModelUseCase::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('AiModelUseCase created', $log->description);
    }

    public function test_logs_activity_on_ai_model_use_case_update(): void
    {
        $modelUseCase = AiModelUseCase::factory()->create(['relationship_type' => 'primary']);

        ActivityLog::truncate();

        $modelUseCase->update(['relationship_type' => 'secondary']);

        $log = ActivityLog::where('actable_id', $modelUseCase->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('relationship_type', $log->changes);
    }

    public function test_logs_activity_on_ai_model_use_case_delete(): void
    {
        $modelUseCase = AiModelUseCase::factory()->create();
        $useCaseId = $modelUseCase->id;

        $modelUseCase->delete();

        $log = ActivityLog::where('actable_id', $useCaseId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
