<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Models\Control;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\ComplianceEvidence;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\ComplianceEvidence\ArtifactType;
use App\Enums\ComplianceEvidence\ReviewOutcome;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComplianceEvidenceControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_list_compliance_evidence(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();
        $project = Project::factory()->create();

        ComplianceEvidence::factory()->count(3)->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/compliance-evidences');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Compliance evidence retrieved successfully',
        ]);
        $this->assertIsArray($response->json('data.data'));
    }

    public function test_user_can_store_compliance_evidence(): void
    {
        $control = Control::factory()->create();
        $project = Project::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();
        $collectedBy = User::factory()->create();

        $payload = [
            'project_id' => $project->id,
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'artifact_uri' => 'https://example.com/evidence.pdf',
            'sample_ids' => ['sample1', 'sample2'],
            'sampling_method' => 'random',
            'collection_period_start' => now()->subDays(10)->toDateString(),
            'collection_period_end' => now()->toDateString(),
            'collected_by' => $collectedBy->id,
            'hash_checksum' => 'abc123def456',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/compliance-evidences', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Compliance evidence created successfully',
        ]);

        $this->assertDatabaseHas('compliance_evidences', [
            'project_id' => $project->id,
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
        ]);
    }

    public function test_user_can_view_single_compliance_evidence(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();
        $project = Project::factory()->create();

        $evidence = ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'project_id' => $project->id,
            'artifact_type' => ArtifactType::LOG->value,
            'artifact_uri' => 'https://example.com/logs.txt',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/compliance-evidences/{$evidence->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Compliance evidence retrieved successfully',
            'data' => [
                'id' => $evidence->id,
                'project_id' => $project->id,
                'control_id' => $control->id,
                'requirement_id' => $requirement->id,
                'ai_model_id' => $aiModel->id,
                'artifact_type' => ArtifactType::LOG->value,
            ],
        ]);
    }

    public function test_user_can_update_compliance_evidence(): void
    {
        $project = Project::factory()->create();
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();
        $reviewedBy = User::factory()->create();

        $evidence = ComplianceEvidence::factory()->create([
            'project_id' => $project->id,
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::LOG->value,
            'review_outcome' => null,
        ]);

        $payload = [
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'review_outcome' => ReviewOutcome::PASS->value,
            'reviewed_by' => $reviewedBy->id,
            'reviewed_at' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->postJson("/api/compliance-evidences/{$evidence->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Compliance evidence updated successfully',
        ]);

        $this->assertDatabaseHas('compliance_evidences', [
            'id' => $evidence->id,
            'project_id' => $project->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'review_outcome' => ReviewOutcome::PASS->value,
        ]);
    }

    public function test_user_can_delete_compliance_evidence(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        $evidence = ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/compliance-evidences/{$evidence->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Compliance evidence deleted successfully',
            'data' => null,
        ]);

        $this->assertDatabaseMissing('compliance_evidences', [
            'id' => $evidence->id,
        ]);
    }

    public function test_list_compliance_evidence_with_artifact_type_filter(): void
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

        $response = $this->actingAs($this->user)->getJson('/api/compliance-evidences?artifact_type='.ArtifactType::DOCUMENT->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_list_compliance_evidence_with_review_outcome_filter(): void
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

        $response = $this->actingAs($this->user)->getJson('/api/compliance-evidences?review_outcome='.ReviewOutcome::PASS->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_list_compliance_evidence_with_pagination(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        ComplianceEvidence::factory()->count(15)->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/compliance-evidences?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(15, $response->json('data.total'));
    }

    public function test_store_compliance_evidence_requires_valid_control(): void
    {
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        $payload = [
            'control_id' => 9999, // Non-existent control
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'artifact_uri' => 'https://example.com/evidence.pdf',
            'sampling_method' => 'random',
            'hash_checksum' => 'abc123',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/compliance-evidences', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['control_id']);
    }

    public function test_store_compliance_evidence_requires_valid_artifact_type(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        $payload = [
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => 'invalid_type',
            'artifact_uri' => 'https://example.com/evidence.pdf',
            'sampling_method' => 'random',
            'hash_checksum' => 'abc123',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/compliance-evidences', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['artifact_type']);
    }

    public function test_store_compliance_evidence_requires_valid_artifact_uri(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        $payload = [
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'artifact_type' => ArtifactType::DOCUMENT->value,
            'artifact_uri' => 'not-a-url',
            'sampling_method' => 'random',
            'hash_checksum' => 'abc123',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/compliance-evidences', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['artifact_uri']);
    }

    public function test_show_compliance_evidence_loads_relationships(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        $evidence = ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/compliance-evidences/{$evidence->id}");

        $response->assertStatus(200);
        $this->assertArrayHasKey('control', $response->json('data'));
        $this->assertArrayHasKey('requirement', $response->json('data'));
        $this->assertArrayHasKey('ai_model', $response->json('data'));
    }

    public function test_update_compliance_evidence_with_partial_data(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        $evidence = ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
            'review_outcome' => null,
        ]);

        $payload = [
            'review_outcome' => ReviewOutcome::PASS->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/compliance-evidences/{$evidence->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('compliance_evidences', [
            'id' => $evidence->id,
            'review_outcome' => ReviewOutcome::PASS->value,
        ]);
    }

    public function test_update_compliance_evidence_requires_valid_review_outcome(): void
    {
        $control = Control::factory()->create();
        $requirement = Requirement::factory()->create();
        $aiModel = AiModel::factory()->create();

        $evidence = ComplianceEvidence::factory()->create([
            'control_id' => $control->id,
            'requirement_id' => $requirement->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $payload = [
            'review_outcome' => 'invalid_outcome',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/compliance-evidences/{$evidence->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['review_outcome']);
    }

    public function test_list_compliance_evidence_with_multiple_filters(): void
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

        $response = $this->actingAs($this->user)->getJson(
            '/api/compliance-evidences?artifact_type='.ArtifactType::DOCUMENT->value.'&review_outcome='.ReviewOutcome::PASS->value
        );

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }
}
