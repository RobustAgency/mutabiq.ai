<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Models\Control;
use App\Models\Requirement;
use App\Models\ComplianceEvidence;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\ComplianceEvidence\ArtifactType;
use App\Enums\ComplianceEvidence\ReviewOutcome;
use App\Repositories\ComplianceEvidenceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComplianceEvidenceRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private ComplianceEvidenceRepository $complianceEvidenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->complianceEvidenceRepository = app(ComplianceEvidenceRepository::class);
    }

    public function test_it_filters_compliance_evidence_by_artifact_type(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
        ]);

        ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::LOG->value,
        ]);

        $results = $this->complianceEvidenceRepository->getFilteredComplianceEvidence([
            'artifact_type' => ArtifactType::DOCUMENT->value,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals(ArtifactType::DOCUMENT->value, $results->first()->artifact_type);
    }

    public function test_it_filters_compliance_evidence_by_review_outcome(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'review_outcome' => ReviewOutcome::PASS->value,
        ]);

        ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'review_outcome' => ReviewOutcome::FAIL->value,
        ]);

        $results = $this->complianceEvidenceRepository->getFilteredComplianceEvidence([
            'review_outcome' => ReviewOutcome::PASS->value,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals(ReviewOutcome::PASS->value, $results->first()->review_outcome);
    }

    public function test_it_applies_pagination_correctly(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        ComplianceEvidence::factory()->count(15)->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $results = $this->complianceEvidenceRepository->getFilteredComplianceEvidence(['per_page' => 5]);

        $this->assertCount(5, $results);
        $this->assertEquals(5, $results->perPage());
        $this->assertEquals(15, $results->total());
    }

    public function test_it_applies_default_pagination(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        ComplianceEvidence::factory()->count(15)->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $results = $this->complianceEvidenceRepository->getFilteredComplianceEvidence();

        $this->assertEquals(10, $results->perPage());
    }

    public function test_it_creates_compliance_evidence(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();
        $collectedBy = User::factory()->create();

        $data = [
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'artifact_uri' => 'https://example.com/evidence.pdf',
            'sample_ids' => json_encode(['sample1', 'sample2']),
            'sampling_method' => 'random',
            'collection_period_start' => now()->subDays(10),
            'collection_period_end' => now(),
            'collected_by' => $collectedBy->id,
            'hash_checksum' => 'abc123def456',
        ];

        $evidence = $this->complianceEvidenceRepository->createComplianceEvidence($data);

        $this->assertNotNull($evidence->id);
        $this->assertEquals($control->id, $evidence->control_id);
        $this->assertEquals($requirement->id, $evidence->requirement_id);
        $this->assertEquals(ArtifactType::DOCUMENT->value, $evidence->artifact_type);
        $this->assertDatabaseHas('compliance_evidences', [
            'id' => $evidence->id,
            'control_id' => $control->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
        ]);
    }

    public function test_it_updates_compliance_evidence(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();
        $reviewedBy = User::factory()->create();

        $evidence = ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::LOG->value,
            'review_outcome' => null,
        ]);

        $data = [
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'review_outcome' => ReviewOutcome::PASS->value,
            'reviewed_by' => $reviewedBy->id,
            'reviewed_at' => now(),
        ];

        $updatedEvidence = $this->complianceEvidenceRepository->updateComplianceEvidence($evidence, $data);

        $this->assertEquals(ArtifactType::DOCUMENT->value, $updatedEvidence->artifact_type);
        $this->assertEquals(ReviewOutcome::PASS->value, $updatedEvidence->review_outcome);
        $this->assertEquals($reviewedBy->id, $updatedEvidence->reviewed_by);
        $this->assertDatabaseHas('compliance_evidences', [
            'id' => $evidence->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'review_outcome' => ReviewOutcome::PASS->value,
        ]);
    }

    public function test_it_deletes_compliance_evidence(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        $evidence = ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $result = $this->complianceEvidenceRepository->deleteComplianceEvidence($evidence);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('compliance_evidences', [
            'id' => $evidence->id,
        ]);
    }

    public function test_it_applies_multiple_filters(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'review_outcome' => ReviewOutcome::PASS->value,
        ]);

        ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::LOG->value,
            'review_outcome' => ReviewOutcome::FAIL->value,
        ]);

        ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'review_outcome' => ReviewOutcome::FAIL->value,
        ]);

        $results = $this->complianceEvidenceRepository->getFilteredComplianceEvidence([
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'review_outcome' => ReviewOutcome::PASS->value,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals(ArtifactType::DOCUMENT->value, $results->first()->artifact_type);
        $this->assertEquals(ReviewOutcome::PASS->value, $results->first()->review_outcome);
    }

    public function test_it_orders_by_latest_first(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        $oldEvidence = ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'created_at' => now()->subDays(10),
        ]);

        $newEvidence = ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'created_at' => now(),
        ]);

        $results = $this->complianceEvidenceRepository->getFilteredComplianceEvidence();

        $this->assertEquals($newEvidence->id, $results->first()->id);
        $this->assertEquals($oldEvidence->id, $results->last()->id);
    }
}
