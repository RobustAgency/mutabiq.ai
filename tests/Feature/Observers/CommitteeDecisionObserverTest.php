<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\CommitteeDecision;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeDecisionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_not_log_without_organization_id(): void
    {
        $decision = CommitteeDecision::factory()->create();

        $log = ActivityLog::where('actable_id', $decision->id)
            ->where('actable_type', CommitteeDecision::class)
            ->first();

        // CommitteeDecision doesn't have organization_id, so no logs are created
        $this->assertNull($log);
    }
}
