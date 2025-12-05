<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\AiModel;
use App\Models\Framework;
use App\Models\RegulatorySubmission;
use App\Enums\RegulatorySubmission\Status;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\RegulatorySubmission\SubmissionType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegulatorySubmissionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
    }

    public function test_super_admin_can_list_regulatory_submissions(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        RegulatorySubmission::factory()->count(3)->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/admin/regulatory-submissions');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Regulatory submissions retrieved successfully',
        ]);
        $this->assertIsArray($response->json('data.data'));
    }

    public function test_super_admin_can_store_regulatory_submission(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $payload = [
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'jurisdiction' => ['US', 'EU'],
            'submission_type' => SubmissionType::REGISTRATION->value,
            'status' => Status::DRAFT->value,
            'content_summary' => 'Test submission for AI model registration',
            'tracking_id' => 'REG-2025-001-TEST',
            'commitments' => ['commitment 1', 'commitment 2'],
            'renewal_due_at' => now()->addYear()->toDateString(),
            'evidence_bundle_ids' => [1, 2, 3],
            'submitted_at' => now()->toDateString(),
            'submitted_by' => $this->user->id,
            'documents_uri' => 'https://example.com/documents/REG-2025-001-TEST',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/regulatory-submissions', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Regulatory submission created successfully',
        ]);

        $this->assertDatabaseHas('regulatory_submissions', [
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'submission_type' => SubmissionType::REGISTRATION->value,
            'tracking_id' => 'REG-2025-001-TEST',
        ]);
    }

    public function test_super_admin_can_view_single_regulatory_submission(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $submission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'EMA',
            'submission_type' => SubmissionType::NOTIFICATION->value,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/admin/regulatory-submissions/{$submission->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Regulatory submission retrieved successfully',
            'data' => [
                'id' => $submission->id,
                'framework_id' => $framework->id,
                'ai_model_id' => $aiModel->id,
                'authority' => 'EMA',
                'submission_type' => SubmissionType::NOTIFICATION->value,
            ],
        ]);
    }

    public function test_super_admin_can_update_regulatory_submission(): void
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

        $payload = [
            'status' => Status::SUBMITTED->value,
            'submitted_by' => $submittedBy->id,
            'submitted_at' => now()->toDateString(),
            'documents_uri' => 'https://example.com/submission.pdf',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/admin/regulatory-submissions/{$submission->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Regulatory submission updated successfully',
        ]);

        $this->assertDatabaseHas('regulatory_submissions', [
            'id' => $submission->id,
            'status' => Status::SUBMITTED->value,
            'submitted_by' => $submittedBy->id,
        ]);
    }

    public function test_super_admin_can_delete_regulatory_submission(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $submission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/admin/regulatory-submissions/{$submission->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Regulatory submission deleted successfully',
            'data' => null,
        ]);

        $this->assertDatabaseMissing('regulatory_submissions', [
            'id' => $submission->id,
        ]);
    }

    public function test_list_regulatory_submissions_with_authority_filter(): void
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

        $response = $this->actingAs($this->user)->getJson('/api/admin/regulatory-submissions?authority=FDA');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_list_regulatory_submissions_with_submission_type_filter(): void
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

        $response = $this->actingAs($this->user)->getJson('/api/admin/regulatory-submissions?submission_type='.SubmissionType::REGISTRATION->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_list_regulatory_submissions_with_status_filter(): void
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

        $response = $this->actingAs($this->user)->getJson('/api/admin/regulatory-submissions?status='.Status::SUBMITTED->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_list_regulatory_submissions_with_pagination(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        RegulatorySubmission::factory()->count(15)->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/admin/regulatory-submissions?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(15, $response->json('data.total'));
    }

    public function test_store_regulatory_submission_requires_valid_framework(): void
    {
        $aiModel = AiModel::factory()->create();

        $payload = [
            'framework_id' => 9999, // Non-existent framework
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'jurisdiction' => ['US'],
            'submission_type' => SubmissionType::REGISTRATION->value,
            'content_summary' => 'Test submission',
            'tracking_id' => 'REG-2025-001',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/regulatory-submissions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['framework_id']);
    }

    public function test_store_regulatory_submission_requires_valid_submission_type(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $payload = [
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'jurisdiction' => ['US'],
            'submission_type' => 'invalid_type',
            'content_summary' => 'Test submission',
            'tracking_id' => 'REG-2025-001',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/regulatory-submissions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['submission_type']);
    }

    public function test_store_regulatory_submission_requires_unique_tracking_id(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'tracking_id' => 'REG-2025-001-UNIQUE',
        ]);

        $payload = [
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'jurisdiction' => ['US'],
            'submission_type' => SubmissionType::REGISTRATION->value,
            'content_summary' => 'Test submission',
            'tracking_id' => 'REG-2025-001-UNIQUE',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/regulatory-submissions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tracking_id']);
    }

    public function test_show_regulatory_submission_loads_relationships(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $submission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/admin/regulatory-submissions/{$submission->id}");

        $response->assertStatus(200);
        $this->assertArrayHasKey('framework', $response->json('data'));
        $this->assertArrayHasKey('ai_model', $response->json('data'));
        $this->assertArrayHasKey('submitted_by_user', $response->json('data'));
    }

    public function test_update_regulatory_submission_with_partial_data(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $submission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'status' => Status::DRAFT->value,
        ]);

        $payload = [
            'status' => Status::APPROVED->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/admin/regulatory-submissions/{$submission->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('regulatory_submissions', [
            'id' => $submission->id,
            'status' => Status::APPROVED->value,
        ]);
    }

    public function test_update_regulatory_submission_requires_valid_status(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $submission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $payload = [
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/admin/regulatory-submissions/{$submission->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function test_list_regulatory_submissions_with_multiple_filters(): void
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

        $response = $this->actingAs($this->user)->getJson(
            '/api/admin/regulatory-submissions?authority=FDA&submission_type='.SubmissionType::REGISTRATION->value.'&status='.Status::DRAFT->value
        );

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_store_regulatory_submission_requires_jurisdiction_array(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $payload = [
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'jurisdiction' => 'not-an-array',
            'submission_type' => SubmissionType::REGISTRATION->value,
            'content_summary' => 'Test submission',
            'tracking_id' => 'REG-2025-001',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/regulatory-submissions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jurisdiction']);
    }

    public function test_store_regulatory_submission_requires_non_empty_jurisdiction(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $payload = [
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
            'authority' => 'FDA',
            'jurisdiction' => [],
            'submission_type' => SubmissionType::REGISTRATION->value,
            'content_summary' => 'Test submission',
            'tracking_id' => 'REG-2025-001',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/admin/regulatory-submissions', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jurisdiction']);
    }

    public function test_update_regulatory_submission_requires_valid_documents_uri(): void
    {
        $framework = Framework::factory()->create();
        $aiModel = AiModel::factory()->create();

        $submission = RegulatorySubmission::factory()->create([
            'framework_id' => $framework->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $payload = [
            'documents_uri' => 'not-a-url',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/admin/regulatory-submissions/{$submission->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['documents_uri']);
    }
}
