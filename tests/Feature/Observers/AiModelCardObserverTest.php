<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\AiModelCard;
use App\Models\Organization;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiModelCardObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_model_card_create(): void
    {
        $org = Organization::factory()->create();
        $card = AiModelCard::factory()->create(['organization_id' => $org->id]);

        $log = ActivityLog::where('actable_id', $card->id)
            ->where('actable_type', AiModelCard::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('AiModelCard created', $log->description);
    }

    public function test_logs_activity_on_ai_model_card_update(): void
    {
        $org = Organization::factory()->create();
        $card = AiModelCard::factory()->create([
            'organization_id' => $org->id,
            'title' => 'Original Title',
        ]);

        ActivityLog::truncate();

        $card->update(['title' => 'Updated Title']);

        $log = ActivityLog::where('actable_id', $card->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('title', $log->changes);
        $this->assertEquals('Original Title', $log->changes['title']['from']);
        $this->assertEquals('Updated Title', $log->changes['title']['to']);
    }

    public function test_logs_activity_on_ai_model_card_delete(): void
    {
        $org = Organization::factory()->create();
        $card = AiModelCard::factory()->create(['organization_id' => $org->id]);
        $cardId = $card->id;

        $card->delete();

        $log = ActivityLog::where('actable_id', $cardId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
