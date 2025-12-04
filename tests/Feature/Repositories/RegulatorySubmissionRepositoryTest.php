<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Models\Framework;
use App\Models\RegulatorySubmission;
use App\Enums\RegulatorySubmission\Status;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\RegulatorySubmission\SubmissionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\RegulatorySubmissionRepository;

class RegulatorySubmissionRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private RegulatorySubmissionRepository $regulatorySubmissionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regulatorySubmissionRepository = app(RegulatorySubmissionRepository::class);
    }

    public function test_it_filters_regulatory_submissions_by_authority(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
        ]);

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'EMA',
        ]);

        $results = $this->regulatorySubmissionRepository->getFilteredRegulatorySubmissions([
            'authority' => 'FDA',
        ]);

        $this->assertCount(1, $results);
        $this->assertStringContainsString('FDA', $results->first()->authority);
    }

    public function test_it_filters_regulatory_submissions_by_submission_type(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'submission_type' => SubmissionType::REGISTRATION->value,
        ]);

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'submission_type' => SubmissionType::RENEWAL->value,
        ]);

        $results = $this->regulatorySubmissionRepository->getFilteredRegulatorySubmissions([
            'submission_type' => SubmissionType::REGISTRATION->value,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals(SubmissionType::REGISTRATION->value, $results->first()->submission_type);
    }

    public function test_it_filters_regulatory_submissions_by_status(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'status' => Status::DRAFT->value,
        ]);

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'status' => Status::SUBMITTED->value,
        ]);

        $results = $this->regulatorySubmissionRepository->getFilteredRegulatorySubmissions([
            'status' => Status::SUBMITTED->value,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals(Status::SUBMITTED->value, $results->first()->status);
    }

    public function test_it_applies_pagination_correctly(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        RegulatorySubmission::factory()->count(15)->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $results = $this->regulatorySubmissionRepository->getFilteredRegulatorySubmissions(['per_page' => 5]);

        $this->assertCount(5, $results);
        $this->assertEquals(5, $results->perPage());
        $this->assertEquals(15, $results->total());
    }

    public function test_it_applies_default_pagination(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        RegulatorySubmission::factory()->count(15)->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $results = $this->regulatorySubmissionRepository->getFilteredRegulatorySubmissions();

        $this->assertEquals(10, $results->perPage());
    }

    public function test_it_creates_regulatory_submission(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();
        $submittedBy = User::factory()->create();

        $data = [
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'jurisdiction' => json_encode(['US']),
            'submission_type' => SubmissionType::REGISTRATION->value,
            'content_summary' => 'Test submission content',
            'tracking_id' => 'REG-0001-TEST',
            'status' => Status::DRAFT->value,
            'commitments' => json_encode(['commitment 1', 'commitment 2']),
            'renewal_due_at' => now()->addYear(),
            'evidence_bundle_ids' => json_encode([1, 2, 3]),
        ];

        $submission = $this->regulatorySubmissionRepository->createRegulatorySubmission($data);

        $this->assertNotNull($submission->id);
        $this->assertEquals($framework->id, $submission->framework_id);
        $this->assertEquals($aiModel->id, $submission->ai_model_id);
        $this->assertEquals('FDA', $submission->authority);
        $this->assertEquals(SubmissionType::REGISTRATION->value, $submission->submission_type);
        $this->assertDatabaseHas('regulatory_submissions', [
            'id' => $submission->id,
            'framework_id' => $framework->id,
            'authority' => 'FDA',
            'tracking_id' => 'REG-0001-TEST',
        ]);
    }

    public function test_it_updates_regulatory_submission(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();
        $submittedBy = User::factory()->create();

        $submission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'status' => Status::DRAFT->value,
            'submitted_by' => null,
            'submitted_at' => null,
        ]);

        $data = [
            'status' => Status::SUBMITTED->value,
            'submitted_by' => $submittedBy->id,
            'submitted_at' => now(),
            'documents_uri' => 'https://example.com/submission.pdf',
        ];

        $updatedSubmission = $this->regulatorySubmissionRepository->updateRegulatorySubmission($submission, $data);

        $this->assertEquals(Status::SUBMITTED->value, $updatedSubmission->status);
        $this->assertEquals($submittedBy->id, $updatedSubmission->submitted_by);
        $this->assertEquals('https://example.com/submission.pdf', $updatedSubmission->documents_uri);
        $this->assertDatabaseHas('regulatory_submissions', [
            'id' => $submission->id,
            'status' => Status::SUBMITTED->value,
            'submitted_by' => $submittedBy->id,
        ]);
    }

    public function test_it_deletes_regulatory_submission(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $submission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $result = $this->regulatorySubmissionRepository->deleteRegulatorySubmission($submission);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('regulatory_submissions', [
            'id' => $submission->id,
        ]);
    }

    public function test_it_applies_multiple_filters(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'submission_type' => SubmissionType::REGISTRATION->value,
            'status' => Status::DRAFT->value,
        ]);

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'EMA',
            'submission_type' => SubmissionType::RENEWAL->value,
            'status' => Status::SUBMITTED->value,
        ]);

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'submission_type' => SubmissionType::RENEWAL->value,
            'status' => Status::SUBMITTED->value,
        ]);

        $results = $this->regulatorySubmissionRepository->getFilteredRegulatorySubmissions([
            'authority' => 'FDA',
            'submission_type' => SubmissionType::REGISTRATION->value,
            'status' => Status::DRAFT->value,
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('FDA', $results->first()->authority);
        $this->assertEquals(SubmissionType::REGISTRATION->value, $results->first()->submission_type);
        $this->assertEquals(Status::DRAFT->value, $results->first()->status);
    }

    public function test_it_orders_by_latest_first(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $oldSubmission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'created_at' => now()->subDays(10),
        ]);

        $newSubmission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'created_at' => now(),
        ]);

        $results = $this->regulatorySubmissionRepository->getFilteredRegulatorySubmissions();

        $this->assertEquals($newSubmission->id, $results->first()->id);
        $this->assertEquals($oldSubmission->id, $results->last()->id);
    }

    public function test_it_eager_loads_relationships(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $submission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $results = $this->regulatorySubmissionRepository->getFilteredRegulatorySubmissions();

        $this->assertTrue($results->first()->relationLoaded('framework'));
        $this->assertTrue($results->first()->relationLoaded('aiModel'));
        $this->assertTrue($results->first()->relationLoaded('submittedBy'));
    }
}
