<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\CommitteeAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeActionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_not_log_without_organization_id(): void
    {
        $action = CommitteeAction::factory()->create();

        $log = ActivityLog::where('actable_id', $action->id)
            ->where('actable_type', CommitteeAction::class)
            ->first();

        // CommitteeAction doesn't have organization_id, so no logs are created
        $this->assertNull($log);
    }
}
