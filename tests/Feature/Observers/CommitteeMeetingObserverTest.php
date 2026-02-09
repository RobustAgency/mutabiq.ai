<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\CommitteeMeeting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeMeetingObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_not_log_without_organization_id(): void
    {
        $meeting = CommitteeMeeting::factory()->create();

        $log = ActivityLog::where('actable_id', $meeting->id)
            ->where('actable_type', CommitteeMeeting::class)
            ->first();

        // CommitteeMeeting doesn't have organization_id, so no logs are created
        $this->assertNull($log);
    }
}
