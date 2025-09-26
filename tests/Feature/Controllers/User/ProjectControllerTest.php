<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\GovernancePillar;
use App\Enums\UserProjectRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Project;
use App\Models\Framework;
use App\Models\Requirement;
use App\Models\Control;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_get_project_with_total_requirements_and_controls(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $project = Project::factory()->create();
        $project->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);
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

        $response = $this->getJson("/api/projects/{$project->id}");
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'governance_pillar',
                'progress',
                'total_requirements',
                'total_controls',
                'frameworks' => [
                    '*' => [
                        'id',
                        'name',
                        'requirements',
                        'controls',
                    ],
                ],
            ],
        ]);
    }

    public function test_user_can_get_all_project_list(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Project::factory()->count(3)->create()->each(function ($project) use ($user) {
            $project->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);
        });

        $response = $this->getJson('/api/projects');
        $response->assertOk();
        $response->assertJsonCount(3, 'data.data'); // Adjust based on pagination structure
    }

    public function test_user_can_get_projects_with_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Project::factory()->count(10)->create()->each(function ($project) use ($user) {
            $project->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);
        });

        $response = $this->getJson('/api/projects?per_page=5');
        $response->assertOk();
        $response->assertJsonCount(5, 'data.data');
    }

    public function test_user_can_create_project(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $projectData = [
            'name' => 'Project Name',
            'description' => $this->faker->paragraph,
            'governance_pillar' => GovernancePillar::AI_GOVERNANCE,
        ];

        $response = $this->postJson('/api/projects', $projectData);
        $response->assertStatus(201);
        $response->assertJson(['error' => false, 'message' => 'Project created successfully']);
        $this->assertDatabaseHas('projects', ['name' => $projectData['name']]);
        $this->assertDatabaseHas('project_user', ['user_id' => $user->id, 'role' => UserProjectRole::OWNER]);
    }

    public function test_project_owner_can_add_a_new_member(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $project = Project::factory()->create();
        $project->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);

        $newMember = User::factory()->create();
        $memberData = [
            'user_id' => $newMember->id,
            'role' => UserProjectRole::EDITOR,
        ];

        $response = $this->postJson("/api/projects/{$project->id}/add-member", $memberData);
        $response->assertOk();
        $response->assertJson(['error' => false, 'message' => 'Member added to project successfully']);
        $this->assertDatabaseHas('project_user', ['user_id' => $newMember->id, 'role' => UserProjectRole::EDITOR]);
    }

    public function test_user_can_add_frameworks_to_project(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $project = Project::factory()->create();
        $project->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);

        $frameworks = Framework::factory()->count(2)->create();
        $frameworkIDs = $frameworks->pluck('id')->toArray();
        $data = ['framework_ids' => $frameworkIDs];

        $response = $this->postJson("/api/projects/{$project->id}/add-frameworks", $data);
        $response->assertOk();
        $response->assertJson(['error' => false, 'message' => 'Frameworks added to project successfully']);
        foreach ($frameworkIDs as $fwID) {
            $this->assertDatabaseHas('framework_project', ['project_id' => $project->id, 'framework_id' => $fwID]);
        }
    }

    public function test_non_owner_cannot_add_a_new_member(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $project = Project::factory()->create();
        $project->users()->attach($owner->id, ['role' => UserProjectRole::OWNER]);

        $nonOwner = User::factory()->create();
        $project->users()->attach($nonOwner->id, ['role' => UserProjectRole::EDITOR]);
        $this->actingAs($nonOwner);

        $newMember = User::factory()->create();
        $memberData = [
            'user_id' => $newMember->id,
            'role' => UserProjectRole::EDITOR,
        ];

        $response = $this->postJson("/api/projects/{$project->id}/add-member", $memberData);
        $response->assertForbidden();
    }

    public function test_it_return_the_user_with__project_user_role(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $this->actingAs($user1);
        $project = Project::factory()->create();
        $project->users()->attach($user1->id, ['role' => UserProjectRole::OWNER]);
        $project->users()->attach($user2->id, ['role' => UserProjectRole::EDITOR]);
        $project->users()->attach($user3->id, ['role' => UserProjectRole::REVIEWER]);

        $response = $this->getJson("/api/projects/{$project->id}");
        $response->assertOk();
        $response->assertJsonPath('data.users.0.project_role', UserProjectRole::OWNER);
        $response->assertJsonPath('data.users.1.project_role', UserProjectRole::EDITOR);
        $response->assertJsonPath('data.users.2.project_role', UserProjectRole::REVIEWER);
    }
}
