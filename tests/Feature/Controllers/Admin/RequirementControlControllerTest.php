<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\AiModel;
use App\Models\Control;
use App\Models\Requirement;
use App\Models\RequirementControl;
use App\Enums\RequirementControl\Coverage;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\RequirementControl\ReviewStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequirementControlControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
    }

    public function test_super_admin_can_list_requirement_controls(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        RequirementControl::factory()->count(3)->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/admin/requirement-controls');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement controls retrieved successfully',
        ]);
        $this->assertIsArray($response->json('data.data'));
    }

    public function test_super_admin_can_store_requirement_control(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();
        $aiModel = AiModel::factory()->create();

        $payload = [
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'ai_model_id' => $aiModel->id,
            'coverage' => Coverage::FULL->value,
            'interpretation_notes' => 'This control fully addresses the requirement',
            'residual_gaps' => 'No residual gaps identified',
            'review_status' => ReviewStatus::DRAFT->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/requirement-controls', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement control created successfully',
        ]);

        $this->assertDatabaseHas('requirement_controls', [
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'ai_model_id' => $aiModel->id,
            'coverage' => Coverage::FULL->value,
        ]);
    }

    public function test_super_admin_can_view_single_requirement_control(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();
        $aiModel = AiModel::factory()->create();

        $requirementControl = RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'ai_model_id' => $aiModel->id,
            'coverage' => Coverage::PARTIAL->value,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/admin/requirement-controls/{$requirementControl->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement control retrieved successfully',
            'data' => [
                'id' => $requirementControl->id,
                'requirement_id' => $requirement->id,
                'control_id' => $control->id,
                'ai_model_id' => $aiModel->id,
                'coverage' => Coverage::PARTIAL->value,
            ],
        ]);
    }

    public function test_super_admin_can_update_requirement_control(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        $requirementControl = RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'coverage' => Coverage::PARTIAL->value,
            'review_status' => ReviewStatus::DRAFT->value,
        ]);

        $payload = [
            'coverage' => Coverage::FULL->value,
            'review_status' => ReviewStatus::APPROVED->value,
            'interpretation_notes' => 'Updated interpretation',
            'residual_gaps' => 'No gaps remaining',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/admin/requirement-controls/{$requirementControl->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement control updated successfully',
        ]);

        $this->assertDatabaseHas('requirement_controls', [
            'id' => $requirementControl->id,
            'coverage' => Coverage::FULL->value,
            'review_status' => ReviewStatus::APPROVED->value,
            'interpretation_notes' => 'Updated interpretation',
        ]);
    }

    public function test_super_admin_can_delete_requirement_control(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        $requirementControl = RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/admin/requirement-controls/{$requirementControl->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Requirement control deleted successfully',
            'data' => null,
        ]);

        $this->assertDatabaseMissing('requirement_controls', [
            'id' => $requirementControl->id,
        ]);
    }

    public function test_list_requirement_controls_with_requirement_id_filter(): void
    {
        $requirement1 = Requirement::factory()->create();
        $requirement2 = Requirement::factory()->create();
        $control = Control::factory()->create();

        RequirementControl::factory()->create([
            'requirement_id' => $requirement1->id,
            'control_id' => $control->id,
        ]);

        RequirementControl::factory()->create([
            'requirement_id' => $requirement2->id,
            'control_id' => $control->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/admin/requirement-controls?requirement_id={$requirement1->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_list_requirement_controls_with_control_id_filter(): void
    {
        $requirement = Requirement::factory()->create();
        $control1 = Control::factory()->create();
        $control2 = Control::factory()->create();

        RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control1->id,
        ]);

        RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control2->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/admin/requirement-controls?control_id={$control1->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_list_requirement_controls_with_coverage_filter(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'coverage' => Coverage::FULL->value,
        ]);

        RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'coverage' => Coverage::PARTIAL->value,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/admin/requirement-controls?coverage='.Coverage::FULL->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_list_requirement_controls_with_review_status_filter(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'review_status' => ReviewStatus::DRAFT->value,
        ]);

        RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'review_status' => ReviewStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/admin/requirement-controls?review_status='.ReviewStatus::APPROVED->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_list_requirement_controls_with_pagination(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        RequirementControl::factory()->count(15)->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/admin/requirement-controls?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(15, $response->json('data.total'));
    }

    public function test_store_requirement_control_requires_valid_requirement(): void
    {
        $control = Control::factory()->create();

        $payload = [
            'requirement_id' => 9999, // Non-existent requirement
            'control_id' => $control->id,
            'model_scope' => 'Test Scope',
            'coverage' => Coverage::FULL->value,
            'review_status' => ReviewStatus::DRAFT->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/requirement-controls', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['requirement_id']);
    }

    public function test_store_requirement_control_requires_valid_control(): void
    {
        $requirement = Requirement::factory()->create();

        $payload = [
            'requirement_id' => $requirement->id,
            'control_id' => 9999, // Non-existent control
            'model_scope' => 'Test Scope',
            'coverage' => Coverage::FULL->value,
            'review_status' => ReviewStatus::DRAFT->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/requirement-controls', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['control_id']);
    }

    public function test_store_requirement_control_requires_valid_coverage(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        $payload = [
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'model_scope' => 'Test Scope',
            'coverage' => 'invalid_coverage',
            'review_status' => ReviewStatus::DRAFT->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/requirement-controls', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['coverage']);
    }

    public function test_store_requirement_control_requires_valid_review_status(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        $payload = [
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'model_scope' => 'Test Scope',
            'coverage' => Coverage::FULL->value,
            'review_status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/requirement-controls', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['review_status']);
    }

    public function test_show_requirement_control_loads_relationships(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        $requirementControl = RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/admin/requirement-controls/{$requirementControl->id}");

        $response->assertStatus(200);
        $this->assertArrayHasKey('requirement', $response->json('data'));
        $this->assertArrayHasKey('control', $response->json('data'));
    }

    public function test_update_requirement_control_with_partial_data(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        $requirementControl = RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'coverage' => Coverage::PARTIAL->value,
        ]);

        $payload = [
            'coverage' => Coverage::FULL->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/admin/requirement-controls/{$requirementControl->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('requirement_controls', [
            'id' => $requirementControl->id,
            'coverage' => Coverage::FULL->value,
        ]);
    }
}
