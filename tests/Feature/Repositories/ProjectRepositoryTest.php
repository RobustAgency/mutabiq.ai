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

    private ProjectRepository $projectRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRepository = app(ProjectRepository::class);
    }

    public function test_it_get_project_by_id(): void
    {

        $framework = Framework::factory()->create();


        $requirements1 = Requirement::factory()->count(2)->create();
        $requirements2 = Requirement::factory()->count(3)->create();
        $framework->requirements()->attach($requirements1->pluck('id'));
        $framework->requirements()->attach($requirements2->pluck('id'));

        $controls1 = Control::factory()->count(1)->create();
        $controls2 = Control::factory()->count(4)->create();
        $framework->controls()->attach($controls1->pluck('id'));
        $framework->controls()->attach($controls2->pluck('id'));

        $project = Project::factory()->create([
            'framework_id' => $framework->id,
        ]);


        $result = $this->projectRepository->getProjectByID($project);
        $this->assertEquals(5, $result->framework->requirements_count);
        $this->assertEquals(5, $result->framework->controls_count);
    }

    public function test_it_get_project_by_id_with_no_framework(): void
    {
        $project = Project::factory()->create();
        $result = $this->projectRepository->getProjectByID($project);
        $this->assertEquals(0, $result->total_requirements);
        $this->assertEquals(0, $result->total_controls);
    }

    public function test_it_get_project_by_id_with_framework_but_no_requirements_or_controls(): void
    {
        $project = Project::factory()->create();
        $result = $this->projectRepository->getProjectByID($project);
        $this->assertEquals(0, $result->total_requirements);
        $this->assertEquals(0, $result->total_controls);
    }

    public function test_it_get_projects_by_user_id_having_different_roles(): void
    {
        $user = User::factory()->create();
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();
        $user->projects()->attach($project1->id, ['role' => UserProjectRole::OWNER]);
        $user->projects()->attach($project2->id, ['role' => UserProjectRole::EDITOR]);
        $this->assertCount(2, $this->projectRepository->getFilteredProjects($user->id));
    }

    public function test_it_filter_projects_by_name(): void
    {
        $user = User::factory()->create();

        Project::factory()->create([
            'name' => 'AI Governance Project',
        ])->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);

        Project::factory()->create([
            'name' => 'Data Privacy Project',
        ])->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);

        $results = $this->projectRepository->getFilteredProjects($user->id, ['name' => 'AI']);

        $this->assertCount(1, $results);
        $this->assertEquals('AI Governance Project', $results->first()->name);
    }

    public function test_it_filter_project_by_governance_pillar(): void
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

        $results = $this->projectRepository->getFilteredProjects($user->id, ['governance_pillar' => GovernancePillar::AI_GOVERNANCE]);

        $this->assertCount(1, $results);
        $this->assertEquals('AI Governance Project', $results->first()->name);
    }

    public function test_it_create_project_and_assign_owner(): void
    {
        $user = User::factory()->create();
        $projectData = [
            'name' => 'New Project',
            'description' => 'Project Description',
            'governance_pillar' => GovernancePillar::AI_GOVERNANCE,
            'progress' => 0,
        ];

        $project = $this->projectRepository->createProject($user, $projectData);

        $this->assertDatabaseHas('projects', ['name' => 'New Project']);
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => UserProjectRole::OWNER,
        ]);
    }

    public function test_it_assign_single_framework_to_project(): void
    {
        $project = Project::factory()->create();
        $framework = Framework::factory()->create();

        $this->projectRepository->addFrameworkToProject($project, $framework->id);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'framework_id' => $framework->id,
        ]);
    }

    public function test_it_add_member_to_project(): void
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();

        $this->projectRepository->addMemberToProject($project, [
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
