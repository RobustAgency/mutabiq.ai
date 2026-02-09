<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\AiRiskRegister;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiRiskRegisterObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_risk_register_create(): void
    {
        $register = AiRiskRegister::factory()->create();

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('actable_type', AiRiskRegister::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('AiRiskRegister created', $log->description);
    }

    public function test_logs_activity_on_ai_risk_register_update(): void
    {
        $register = AiRiskRegister::factory()->create(['status' => 'open']);

        ActivityLog::truncate();

        $register->update(['status' => 'closed']);

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('status', $log->changes);
    }

    public function test_logs_activity_on_ai_risk_register_delete(): void
    {
        $register = AiRiskRegister::factory()->create();
        $registerId = $register->id;

        $register->delete();

        $log = ActivityLog::where('actable_id', $registerId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
