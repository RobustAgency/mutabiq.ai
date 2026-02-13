<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\AiModel;
use App\Models\Project;
use App\Models\Framework;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_project_create(): void
    {
        $project = Project::factory()->create();

        $log = ActivityLog::where('actable_id', $project->id)
            ->where('actable_type', Project::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('Project created', $log->description);
    }

    public function test_logs_activity_on_project_update(): void
    {
        $project = Project::factory()->create(['progress' => 0]);

        ActivityLog::truncate();

        $project->update(['progress' => 50]);

        $log = ActivityLog::where('actable_id', $project->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('Project updated', $log->description);
        $this->assertArrayHasKey('progress', $log->changes);
        $this->assertEquals(0, $log->changes['progress']['from']);
        $this->assertEquals(50, $log->changes['progress']['to']);
    }

    public function test_logs_activity_on_project_delete(): void
    {
        $project = Project::factory()->create();
        $projectId = $project->id;

        $project->delete();

        $log = ActivityLog::where('actable_id', $projectId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('Project deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $project = Project::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
            'governance_pillar' => 'CONTROL',
            'progress' => 0,
        ]);

        ActivityLog::truncate();

        $project->update([
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'governance_pillar' => 'MONITORING',
            'progress' => 75,
        ]);

        $log = ActivityLog::where('actable_id', $project->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertArrayHasKey('description', $log->changes);
        $this->assertArrayHasKey('governance_pillar', $log->changes);
        $this->assertArrayHasKey('progress', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $project = Project::factory()->create();

        $log = ActivityLog::where('actable_id', $project->id)
            ->where('actable_type', Project::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($project->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $project = Project::factory()->create();

        $log = ActivityLog::where('actable_id', $project->id)
            ->where('actable_type', Project::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_framework_assignment_changes(): void
    {
        $framework1 = Framework::factory()->create();
        $framework2 = Framework::factory()->create();
        $project = Project::factory()->create(['framework_id' => $framework1->id]);

        ActivityLog::truncate();

        $project->update(['framework_id' => $framework2->id]);

        $log = ActivityLog::where('actable_id', $project->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('framework_id', $log->changes);
        $this->assertEquals($framework1->id, $log->changes['framework_id']['from']);
        $this->assertEquals($framework2->id, $log->changes['framework_id']['to']);
    }

    public function test_tracks_ai_model_assignment_changes(): void
    {
        $aiModel = AiModel::factory()->create();
        $project = Project::factory()->create();

        ActivityLog::truncate();

        $project->update(['ai_model_id' => $aiModel->id]);

        $log = ActivityLog::where('actable_id', $project->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('ai_model_id', $log->changes);
    }
}
