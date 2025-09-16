<?php

namespace Tests\Feature\Repositories;

use App\Enums\UserProjectRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Project;
use App\Models\Framework;
use App\Models\Requirement;
use App\Models\Control;
use App\Repositories\ProjectRepository;
use App\Models\User;
use App\Enums\GovernancePillar;

class ProjectRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    public function test_it_can_get_project_by_id()
    {
        $project = Project::factory()->create();
        $framework1 = Framework::factory()->create();
        $framework2 = Framework::factory()->create();
        $project->frameworks()->attach([$framework1->id, $framework2->id]);

        $requirements1 = Requirement::factory()->count(2)->create();
        $requirements2 = Requirement::factory()->count(3)->create();
        $framework1->requirements()->attach($requirements1->pluck('id'));
        $framework2->requirements()->attach($requirements2->pluck('id'));

        $controls1 = Control::factory()->count(1)->create();
        $controls2 = Control::factory()->count(4)->create();
        $framework1->controls()->attach($controls1->pluck('id'));
        $framework2->controls()->attach($controls2->pluck('id'));

        $repo = new ProjectRepository();
        $result = $repo->getProjectByID($project->id);

        $this->assertEquals(5, $result['total_requirements']);
        $this->assertEquals(5, $result['total_controls']);
    }

    public function test_it_can_get_project_by_id_with_no_frameworks()
    {
        $project = Project::factory()->create();
        $repo = new ProjectRepository();
        $result = $repo->getProjectByID($project->id);
        $this->assertEquals(0, $result['total_requirements']);
        $this->assertEquals(0, $result['total_controls']);
    }

    public function test_it_can_get_project_by_id_with_frameworks_but_no_requirements_or_controls()
    {
        $project = Project::factory()->create();
        $framework = Framework::factory()->create();
        $project->frameworks()->attach($framework->id);
        $repo = new ProjectRepository();
        $result = $repo->getProjectByID($project->id);
        $this->assertEquals(0, $result['total_requirements']);
        $this->assertEquals(0, $result['total_controls']);
    }

    public function test_it_can_get_projects_by_user_id_having_different_roles()
    {
        $user = User::factory()->create();
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();
        $user->projects()->attach($project1->id, ['role' => UserProjectRole::OWNER]);
        $user->projects()->attach($project2->id, ['role' => UserProjectRole::EDITOR]);
        $repository = app(ProjectRepository::class);
        $this->assertCount(2, $repository->getFilteredProjects($user->id));
    }

    public function test_it_can_filter_projects_by_name()
    {
        $user = User::factory()->create();

        Project::factory()->create([
            'name' => 'AI Governance Project',
        ])->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);

        Project::factory()->create([
            'name' => 'Data Privacy Project',
        ])->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);

        $repository = app(ProjectRepository::class);
        $results = $repository->getFilteredProjects($user->id, ['name' => 'AI']);

        $this->assertCount(1, $results);
        $this->assertEquals('AI Governance Project', $results->first()->name);
    }

    public function test_it_can_filter_project_by_governance_pillar()
    {
        $user = User::factory()->create();

        Project::factory()->create([
            'name' => 'AI Governance Project',
            'governance_pillar' => GovernancePillar::AI_GOVERNANCE,
        ])->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);

        Project::factory()->create([
            'name' => 'Data Privacy Project',
            'governance_pillar' => GovernancePillar::DATA_GOVERNANCE,
        ])->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);

        $repository = app(ProjectRepository::class);
        $results = $repository->getFilteredProjects($user->id, ['governance_pillar' => GovernancePillar::AI_GOVERNANCE]);

        $this->assertCount(1, $results);
        $this->assertEquals('AI Governance Project', $results->first()->name);
    }

    public function test_it_can_create_project_and_assign_owner() {
        $user = User::factory()->create();
        $projectData = [
            'name' => 'New Project',
            'description' => 'Project Description',
            'governance_pillar' => GovernancePillar::AI_GOVERNANCE,
            'progress' => 0,
        ];

        $repository = app(ProjectRepository::class);
        $project = $repository->createProject($user, $projectData);

        $this->assertDatabaseHas('projects', ['name' => 'New Project']);
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => UserProjectRole::OWNER,
        ]);
    }

    public function test_it_can_assign_multiple_frameworks_to_project() {
        $project = Project::factory()->create();
        $framework1 = Framework::factory()->create();
        $framework2 = Framework::factory()->create();

        $repository = app(ProjectRepository::class);
        $repository->addFrameworksToProject($project, [$framework1->id, $framework2->id]);

        $this->assertDatabaseHas('framework_project', [
            'project_id' => $project->id,
            'framework_id' => $framework1->id,
        ]);
        $this->assertDatabaseHas('framework_project', [
            'project_id' => $project->id,
            'framework_id' => $framework2->id,
        ]);
    }

    public function test_it_can_add_member_to_project() {
        $project = Project::factory()->create();
        $user = User::factory()->create();

        $repository = app(ProjectRepository::class);
        $repository->addMemberToProject($project, [
            'user_id' => $user->id,
            'role' => UserProjectRole::REVIEWER,
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => UserProjectRole::REVIEWER,
        ]);
    }
}
