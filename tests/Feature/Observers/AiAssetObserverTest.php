<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\AiAsset;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiAssetObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_asset_create(): void
    {
        $aiAsset = AiAsset::factory()->create();

        $log = ActivityLog::where('actable_id', $aiAsset->id)
            ->where('actable_type', AiAsset::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('AiAsset created', $log->description);
    }

    public function test_logs_activity_on_ai_asset_update(): void
    {
        $aiAsset = AiAsset::factory()->create();

        ActivityLog::truncate();

        $aiAsset->update(['vendor_effective_from' => now()]);

        $log = ActivityLog::where('actable_id', $aiAsset->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('vendor_effective_from', $log->changes);
    }

    public function test_logs_activity_on_ai_asset_delete(): void
    {
        $aiAsset = AiAsset::factory()->create();
        $assetId = $aiAsset->id;

        $aiAsset->delete();

        $log = ActivityLog::where('actable_id', $assetId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
