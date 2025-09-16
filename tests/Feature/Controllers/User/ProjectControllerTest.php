<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\GovernancePillar;
use App\Enums\UserProjectRole;
use App\Enums\UserRole;
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

    public function test_user_can_get_project_with_total_requirements_and_controls()
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
        $response->assertJsonFragment([
            'total_requirements' => 5,
            'total_controls' => 5,
        ]);
    }

    public function test_user_can_get_all_project_list()
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

    public function test_user_can_create_project()
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

    public function test_project_owner_can_add_a_new_member()
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

    public function test_user_can_add_frameworks_to_project()
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

    public function test_non_owner_cannot_add_a_new_member()
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
}
