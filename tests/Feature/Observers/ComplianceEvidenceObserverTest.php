<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\Project;
use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\ComplianceEvidence;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComplianceEvidenceObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_compliance_evidence_create(): void
    {
        $organization = Organization::factory()->create();
        $project = Project::factory()->for($organization)->create();
        $evidence = ComplianceEvidence::factory()->for($project)->create();

        $log = ActivityLog::where('actable_id', $evidence->id)
            ->where('actable_type', ComplianceEvidence::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($organization->id, $log->organization_id);
    }

    public function test_logs_activity_on_compliance_evidence_update(): void
    {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create(['organization_id' => $organization->id]);
        $evidence = ComplianceEvidence::factory()->create(['project_id' => $project->id]);

        ActivityLog::truncate();

        $evidence->update(['sampling_method' => 'random-sampling']);

        $log = ActivityLog::where('actable_id', $evidence->id)
            ->where('actable_type', ComplianceEvidence::class)
            ->where('action', 'update')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($organization->id, $log->organization_id);
        $this->assertIsArray($log->changes);
    }

    public function test_logs_activity_on_compliance_evidence_delete(): void
    {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create(['organization_id' => $organization->id]);
        $evidence = ComplianceEvidence::factory()->create(['project_id' => $project->id]);
        $evidenceId = $evidence->id;

        $evidence->delete();

        $log = ActivityLog::where('actable_id', $evidenceId)
            ->where('actable_type', ComplianceEvidence::class)
            ->where('action', 'delete')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($organization->id, $log->organization_id);
    }
}
