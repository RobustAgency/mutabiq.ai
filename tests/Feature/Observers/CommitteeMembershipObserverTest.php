<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\CommitteeMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeMembershipObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_not_log_without_organization_id(): void
    {
        $membership = CommitteeMembership::factory()->create();

        $log = ActivityLog::where('actable_id', $membership->id)
            ->where('actable_type', CommitteeMembership::class)
            ->first();

        // CommitteeMembership doesn't have organization_id, so no logs are created
        $this->assertNull($log);
    }
}
