<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\ConsentCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConsentCoverageObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_consent_coverage_create(): void
    {
        $coverage = ConsentCoverage::factory()->create();

        $log = ActivityLog::where('actable_id', $coverage->id)
            ->where('actable_type', ConsentCoverage::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($coverage->organization_id, $log->organization_id);
    }

    public function test_logs_activity_on_consent_coverage_update(): void
    {
        $coverage = ConsentCoverage::factory()->create();

        ActivityLog::truncate();

        $coverage->update(['coverage_pct' => 95.50]);

        $log = ActivityLog::where('actable_id', $coverage->id)
            ->where('actable_type', ConsentCoverage::class)
            ->where('action', 'update')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($coverage->organization_id, $log->organization_id);
        $this->assertIsArray($log->changes);
    }

    public function test_logs_activity_on_consent_coverage_delete(): void
    {
        $coverage = ConsentCoverage::factory()->create();
        $coverageId = $coverage->id;
        $organizationId = $coverage->organization_id;

        $coverage->delete();

        $log = ActivityLog::where('actable_id', $coverageId)
            ->where('actable_type', ConsentCoverage::class)
            ->where('action', 'delete')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($organizationId, $log->organization_id);
    }
}
