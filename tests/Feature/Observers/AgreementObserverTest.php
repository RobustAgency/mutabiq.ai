<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\Agreement;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgreementObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_agreement_create(): void
    {
        $agreement = Agreement::factory()->create();

        $log = ActivityLog::where('actable_id', $agreement->id)
            ->where('actable_type', Agreement::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('Agreement created', $log->description);
    }

    public function test_logs_activity_on_agreement_update(): void
    {
        $agreement = Agreement::factory()->create(['status' => 'DRAFT']);

        ActivityLog::truncate();

        $agreement->update(['status' => 'ACTIVE']);

        $log = ActivityLog::where('actable_id', $agreement->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertArrayHasKey('status', $log->changes);
    }

    public function test_logs_activity_on_agreement_delete(): void
    {
        $agreement = Agreement::factory()->create();
        $agreementId = $agreement->id;

        $agreement->delete();

        $log = ActivityLog::where('actable_id', $agreementId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
    }
}
