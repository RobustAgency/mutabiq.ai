<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Models\Control;
use App\Models\Requirement;
use App\Models\RequirementControl;
use App\Enums\RequirementControl\Coverage;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\RequirementControl\ReviewStatus;
use App\Repositories\RequirementControlRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequirementControlRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private RequirementControlRepository $requirementControlRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requirementControlRepository = app(RequirementControlRepository::class);
    }

    public function test_it_filters_requirement_controls_by_requirement_id(): void
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

        $results = $this->requirementControlRepository->getFilteredRequirementControls([
            'requirement_id' => $requirement1->id,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals($requirement1->id, $results->first()->requirement_id);
    }

    public function test_it_filters_requirement_controls_by_control_id(): void
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

        $results = $this->requirementControlRepository->getFilteredRequirementControls([
            'control_id' => $control1->id,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals($control1->id, $results->first()->control_id);
    }

    public function test_it_filters_requirement_controls_by_coverage(): void
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

        $results = $this->requirementControlRepository->getFilteredRequirementControls([
            'coverage' => Coverage::FULL->value,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals(Coverage::FULL->value, $results->first()->coverage);
    }

    public function test_it_filters_requirement_controls_by_review_status(): void
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

        $results = $this->requirementControlRepository->getFilteredRequirementControls([
            'review_status' => ReviewStatus::APPROVED->value,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals(ReviewStatus::APPROVED->value, $results->first()->review_status);
    }

    public function test_it_applies_pagination_correctly(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        RequirementControl::factory()->count(15)->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
        ]);

        $results = $this->requirementControlRepository->getFilteredRequirementControls(['per_page' => 5]);

        $this->assertCount(5, $results);
        $this->assertEquals(5, $results->perPage());
        $this->assertEquals(15, $results->total());
    }

    public function test_it_applies_default_pagination(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        RequirementControl::factory()->count(15)->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
        ]);

        $results = $this->requirementControlRepository->getFilteredRequirementControls();

        $this->assertEquals(10, $results->perPage());
    }

    public function test_it_creates_requirement_control(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();
        $aiModel = AiModel::factory()->create();

        $data = [
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'ai_model_id' => $aiModel->id,
            'coverage' => Coverage::FULL->value,
            'interpretation_notes' => 'This control fully addresses the requirement',
            'residual_gaps' => 'No residual gaps identified',
            'review_status' => ReviewStatus::DRAFT->value,
            'reviewed_by' => User::factory()->create()->id,
            'reviewed_at' => $this->faker->dateTime(),
        ];

        $requirementControl = $this->requirementControlRepository->createRequirementControl($data);

        $this->assertNotNull($requirementControl->id);
        $this->assertEquals($requirement->id, $requirementControl->requirement_id);
        $this->assertEquals($control->id, $requirementControl->control_id);
        $this->assertEquals($aiModel->id, $requirementControl->ai_model_id);
        $this->assertEquals(Coverage::FULL->value, $requirementControl->coverage);
        $this->assertEquals(ReviewStatus::DRAFT->value, $requirementControl->review_status);
        $this->assertDatabaseHas('requirement_controls', [
            'id' => $requirementControl->id,
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
        ]);
    }

    public function test_it_updates_requirement_control(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        $requirementControl = RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
            'coverage' => Coverage::PARTIAL->value,
            'review_status' => ReviewStatus::DRAFT->value,
        ]);

        $data = [
            'coverage' => Coverage::FULL->value,
            'review_status' => ReviewStatus::APPROVED->value,
            'interpretation_notes' => 'Updated interpretation',
            'residual_gaps' => 'No gaps',
        ];

        $updatedControl = $this->requirementControlRepository->updateRequirementControl($requirementControl, $data);

        $this->assertEquals(Coverage::FULL->value, $updatedControl->coverage);
        $this->assertEquals(ReviewStatus::APPROVED->value, $updatedControl->review_status);
        $this->assertEquals('Updated interpretation', $updatedControl->interpretation_notes);
        $this->assertEquals('No gaps', $updatedControl->residual_gaps);
        $this->assertDatabaseHas('requirement_controls', [
            'id' => $requirementControl->id,
            'coverage' => Coverage::FULL->value,
            'review_status' => ReviewStatus::APPROVED->value,
        ]);
    }

    public function test_it_deletes_requirement_control(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        $requirementControl = RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
        ]);

        $result = $this->requirementControlRepository->deleteRequirementControl($requirementControl);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('requirement_controls', [
            'id' => $requirementControl->id,
        ]);
    }

    public function test_it_applies_multiple_filters(): void
    {
        $requirement1 = Requirement::factory()->create();
        $requirement2 = Requirement::factory()->create();
        $control1 = Control::factory()->create();
        $control2 = Control::factory()->create();

        RequirementControl::factory()->create([
            'requirement_id' => $requirement1->id,
            'control_id' => $control1->id,
            'coverage' => Coverage::FULL->value,
            'review_status' => ReviewStatus::APPROVED->value,
        ]);

        RequirementControl::factory()->create([
            'requirement_id' => $requirement1->id,
            'control_id' => $control2->id,
            'coverage' => Coverage::PARTIAL->value,
            'review_status' => ReviewStatus::DRAFT->value,
        ]);

        RequirementControl::factory()->create([
            'requirement_id' => $requirement2->id,
            'control_id' => $control1->id,
            'coverage' => Coverage::FULL->value,
            'review_status' => ReviewStatus::DRAFT->value,
        ]);

        $results = $this->requirementControlRepository->getFilteredRequirementControls([
            'requirement_id' => $requirement1->id,
            'coverage' => Coverage::FULL->value,
            'review_status' => ReviewStatus::APPROVED->value,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals($requirement1->id, $results->first()->requirement_id);
        $this->assertEquals(Coverage::FULL->value, $results->first()->coverage);
        $this->assertEquals(ReviewStatus::APPROVED->value, $results->first()->review_status);
    }

    public function test_it_eager_loads_relationships(): void
    {
        $requirement = Requirement::factory()->create();
        $control = Control::factory()->create();

        RequirementControl::factory()->create([
            'requirement_id' => $requirement->id,
            'control_id' => $control->id,
        ]);

        $results = $this->requirementControlRepository->getFilteredRequirementControls();

        $this->assertTrue($results->first()->relationLoaded('requirement'));
        $this->assertTrue($results->first()->relationLoaded('control'));
        $this->assertTrue($results->first()->relationLoaded('user'));
    }
}
