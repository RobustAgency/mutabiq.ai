<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\AiCommittee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiCommitteeObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_not_log_without_organization_id(): void
    {
        $committee = AiCommittee::factory()->create();

        $log = ActivityLog::where('actable_id', $committee->id)
            ->where('actable_type', AiCommittee::class)
            ->first();

        // AiCommittee doesn't have organization_id, so no logs are created
        $this->assertNull($log);
    }
}
