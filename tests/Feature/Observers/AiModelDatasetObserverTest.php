<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\AiModelDataset;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiModelDatasetObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_model_dataset_create(): void
    {
        $dataset = AiModelDataset::factory()->create();

        $log = ActivityLog::where('actable_id', $dataset->id)
            ->where('actable_type', AiModelDataset::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('AiModelDataset created', $log->description);
    }

    public function test_logs_activity_on_ai_model_dataset_update(): void
    {
        $dataset = AiModelDataset::factory()->create(['role' => 'training']);

        ActivityLog::truncate();

        $dataset->update(['role' => 'validation']);

        $log = ActivityLog::where('actable_id', $dataset->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('role', $log->changes);
    }

    public function test_logs_activity_on_ai_model_dataset_delete(): void
    {
        $dataset = AiModelDataset::factory()->create();
        $datasetId = $dataset->id;

        $dataset->delete();

        $log = ActivityLog::where('actable_id', $datasetId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
