<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\AiRiskTreatment;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiRiskTreatmentObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_ai_risk_treatment_create(): void
    {
        $treatment = AiRiskTreatment::factory()->create();

        $log = ActivityLog::where('actable_id', $treatment->id)
            ->where('actable_type', AiRiskTreatment::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('AiRiskTreatment created', $log->description);
    }

    public function test_logs_activity_on_ai_risk_treatment_update(): void
    {
        $treatment = AiRiskTreatment::factory()->create(['status' => 'pending']);

        ActivityLog::truncate();

        $treatment->update(['status' => 'completed']);

        $log = ActivityLog::where('actable_id', $treatment->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('status', $log->changes);
    }

    public function test_logs_activity_on_ai_risk_treatment_delete(): void
    {
        $treatment = AiRiskTreatment::factory()->create();
        $treatmentId = $treatment->id;

        $treatment->delete();

        $log = ActivityLog::where('actable_id', $treatmentId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
