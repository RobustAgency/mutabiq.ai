<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Models\Dataset;
use App\Models\Organization;
use App\Models\AiModelVersion;
use App\Models\DatasetSnapshot;
use App\Enums\AiModelDataset\Role;
use App\Enums\AiModelDataset\CreatedBy;
use App\Enums\AiModelDataset\LinkageStatus;
use App\Enums\AiModelDataset\CrossBorderCheck;
use App\Enums\AiModelDataset\ConsentCheckStatus;
use App\Enums\AiModelDataset\SpecialCategoryCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiModelDatasetControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    private function validPayload(array $overrides = []): array
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
        ]);
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);
        $snapshot = DatasetSnapshot::factory()->create([
            'dataset_id' => $dataset->id,
            'organization_id' => $this->organization->id,
        ]);

        return array_merge([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TRAIN->value,
            'rows_used' => 5000,
            'training_start_date' => now()->subDays(10)->toDateString(),
            'training_end_date' => now()->subDays(5)->toDateString(),
            'training_duration' => '5 days',
            'compute_resources' => 'GPU x4',
            'cost' => 500.00,
            'consent_check_status' => ConsentCheckStatus::PASSED->value,
            'cross_border_check' => CrossBorderCheck::PASSED->value,
            'special_category_check' => SpecialCategoryCheck::PASSED->value,
            'bias_mitigation_applied' => true,
            'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
            'linkage_status' => LinkageStatus::ACTIVE->value,
            'business_justification' => 'Model training purposes',
        ], $overrides);
    }

    /**
     * Test user can create AI model dataset link with all fields.
     */
    public function test_user_can_create_ai_model_dataset_link_with_all_fields(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'display_id',
                    'organization_id',
                    'ai_model_id',
                    'ai_model_version_id',
                    'dataset_id',
                    'dataset_snapshot_id',
                    'role',
                    'rows_used',
                    'training_start_date',
                    'training_end_date',
                    'training_duration',
                    'compute_resources',
                    'cost',
                    'consent_check_status',
                    'cross_border_check',
                    'special_category_check',
                    'bias_mitigation_applied',
                    'created_by_system',
                    'linkage_status',
                    'business_justification',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'AI model dataset link created successfully',
            ]);

        $this->assertDatabaseHas('ai_model_dataset', [
            'ai_model_id' => $payload['ai_model_id'],
            'ai_model_version_id' => $payload['ai_model_version_id'],
            'role' => $payload['role'],
        ]);
    }

    /**
     * Test user can create link with minimal required fields (pretrain role).
     */
    public function test_user_can_create_link_with_minimal_fields(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::PRETRAIN->value,
            'cross_border_check' => CrossBorderCheck::PASSED->value,
            'special_category_check' => SpecialCategoryCheck::PASSED->value,
            'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
            'linkage_status' => LinkageStatus::ACTIVE->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'AI model dataset link created successfully',
            ]);
    }

    /**
     * Test ai_model_id is required.
     */
    public function test_ai_model_id_is_required(): void
    {
        $payload = $this->validPayload(['ai_model_id' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_model_id']);
    }

    /**
     * Test ai_model_id must exist in ai_models table.
     */
    public function test_ai_model_id_must_exist(): void
    {
        $payload = $this->validPayload(['ai_model_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_model_id']);
    }

    /**
     * Test ai_model_version_id is required.
     */
    public function test_ai_model_version_id_is_required(): void
    {
        $payload = $this->validPayload(['ai_model_version_id' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_model_version_id']);
    }

    /**
     * Test ai_model_version_id must exist in ai_model_versions table.
     */
    public function test_ai_model_version_id_must_exist(): void
    {
        $payload = $this->validPayload(['ai_model_version_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_model_version_id']);
    }

    /**
     * Test dataset_id must exist if provided.
     */
    public function test_dataset_id_must_exist_if_provided(): void
    {
        $payload = $this->validPayload(['dataset_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_id']);
    }

    /**
     * Test dataset_snapshot_id must exist if provided.
     */
    public function test_dataset_snapshot_id_must_exist_if_provided(): void
    {
        $payload = $this->validPayload(['dataset_snapshot_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_snapshot_id']);
    }

    /**
     * Test role is required.
     */
    public function test_role_is_required(): void
    {
        $payload = $this->validPayload(['role' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /**
     * Test role must be valid enum value.
     */
    public function test_role_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['role' => 'invalid_role']);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /**
     * Test all role enum values are accepted.
     */
    public function test_all_role_enum_values_are_accepted(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $snapshot = DatasetSnapshot::factory()->create();
        $dataset = Dataset::factory()->create();

        foreach (Role::cases() as $role) {
            $payload = [
                'ai_model_id' => $aiModel->id,
                'ai_model_version_id' => $aiModelVersion->id,
                'dataset_id' => $dataset->id,
                'role' => $role->value,
                'cross_border_check' => CrossBorderCheck::PASSED->value,
                'special_category_check' => SpecialCategoryCheck::PASSED->value,
                'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
                'linkage_status' => LinkageStatus::ACTIVE->value,
            ];

            // Add snapshot for roles that require it
            if (in_array($role, [Role::TRAIN, Role::VALIDATION, Role::TEST, Role::EVAL_BENCHMARK])) {
                $payload['dataset_snapshot_id'] = $snapshot->id;
            }

            $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test dataset_snapshot_id is required for train role.
     */
    public function test_dataset_snapshot_id_is_required_for_train_role(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'role' => Role::TRAIN->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_snapshot_id']);
    }

    /**
     * Test dataset_snapshot_id is required for validation role.
     */
    public function test_dataset_snapshot_id_is_required_for_validation_role(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'role' => Role::VALIDATION->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_snapshot_id']);
    }

    /**
     * Test dataset_snapshot_id is required for test role.
     */
    public function test_dataset_snapshot_id_is_required_for_test_role(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'role' => Role::TEST->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_snapshot_id']);
    }

    /**
     * Test dataset_snapshot_id is required for eval_benchmark role.
     */
    public function test_dataset_snapshot_id_is_required_for_eval_benchmark_role(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'role' => Role::EVAL_BENCHMARK->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_snapshot_id']);
    }

    /**
     * Test dataset_snapshot_id is optional for pretrain role.
     */
    public function test_dataset_snapshot_id_is_optional_for_pretrain_role(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::PRETRAIN->value,
            'cross_border_check' => CrossBorderCheck::PASSED->value,
            'special_category_check' => SpecialCategoryCheck::PASSED->value,
            'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
            'linkage_status' => LinkageStatus::ACTIVE->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(201);
    }

    /**
     * Test dataset_snapshot_id is optional for fine_tune role.
     */
    public function test_dataset_snapshot_id_is_optional_for_fine_tune_role(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::FINE_TUNE->value,
            'cross_border_check' => CrossBorderCheck::PASSED->value,
            'special_category_check' => SpecialCategoryCheck::PASSED->value,
            'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
            'linkage_status' => LinkageStatus::ACTIVE->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(201);
    }

    /**
     * Test consent_check_status must be valid enum if provided.
     */
    public function test_consent_check_status_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['consent_check_status' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['consent_check_status']);
    }

    /**
     * Test all consent_check_status enum values are accepted.
     */
    public function test_all_consent_check_status_enum_values_are_accepted(): void
    {
        foreach (ConsentCheckStatus::cases() as $status) {
            $payload = $this->validPayload(['consent_check_status' => $status->value]);

            $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test cross_border_check must be valid enum.
     */
    public function test_cross_border_check_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['cross_border_check' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cross_border_check']);
    }

    /**
     * Test all cross_border_check enum values are accepted.
     */
    public function test_all_cross_border_check_enum_values_are_accepted(): void
    {
        foreach (CrossBorderCheck::cases() as $status) {
            $payload = $this->validPayload(['cross_border_check' => $status->value]);

            $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test special_category_check must be valid enum if provided.
     */
    public function test_special_category_check_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['special_category_check' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['special_category_check']);
    }

    /**
     * Test all special_category_check enum values are accepted.
     */
    public function test_all_special_category_check_enum_values_are_accepted(): void
    {
        foreach (SpecialCategoryCheck::cases() as $status) {
            $payload = $this->validPayload(['special_category_check' => $status->value]);

            $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test linkage_status must be valid enum if provided.
     */
    public function test_linkage_status_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['linkage_status' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['linkage_status']);
    }

    /**
     * Test all linkage_status enum values are accepted.
     */
    public function test_all_linkage_status_enum_values_are_accepted(): void
    {
        foreach (LinkageStatus::cases() as $status) {
            $payload = $this->validPayload(['linkage_status' => $status->value]);

            $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test unauthenticated user cannot create dataset link.
     */
    public function test_unauthenticated_user_cannot_create_dataset_link(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test user can retrieve paginated AI model datasets.
     */
    public function test_user_can_retrieve_paginated_ai_model_datasets(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $snapshot = DatasetSnapshot::factory()->create();
        $dataset = Dataset::factory()->create();

        // Create multiple dataset links
        for ($i = 0; $i < 25; $i++) {
            $payload = [
                'ai_model_id' => $aiModel->id,
                'ai_model_version_id' => $aiModelVersion->id,
                'dataset_id' => $dataset->id,
                'dataset_snapshot_id' => $snapshot->id,
                'role' => Role::TRAIN->value,
                'cross_border_check' => CrossBorderCheck::PASSED->value,
                'special_category_check' => SpecialCategoryCheck::PASSED->value,
                'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
                'linkage_status' => LinkageStatus::ACTIVE->value,
            ];
            $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        }

        $response = $this->actingAs($this->user)->getJson('/api/ai-model-datasets?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI model datasets retrieved successfully',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total',
                ],
            ]);

        $this->assertEquals(10, count($response->json('data.data')));
    }

    /**
     * Test user can retrieve a single AI model dataset.
     */
    public function test_user_can_retrieve_single_ai_model_dataset(): void
    {
        $payload = $this->validPayload();

        $createResponse = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        $datasetId = $createResponse->json('data.id');

        $response = $this->actingAs($this->user)->getJson("/api/ai-model-datasets/{$datasetId}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI model dataset retrieved successfully',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'display_id',
                    'organization_id',
                    'ai_model_id',
                    'ai_model_version_id',
                    'dataset_id',
                    'dataset_snapshot_id',
                    'role',
                    'rows_used',
                    'training_start_date',
                    'training_end_date',
                    'training_duration',
                    'compute_resources',
                    'cost',
                    'consent_check_status',
                    'cross_border_check',
                    'special_category_check',
                    'bias_mitigation_applied',
                    'created_by_system',
                    'linkage_status',
                    'business_justification',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $datasetId)
            ->assertJsonPath('data.role', $payload['role']);
    }

    /**
     * Test unauthenticated user cannot retrieve AI model datasets.
     */
    public function test_unauthenticated_user_cannot_retrieve_datasets(): void
    {
        $response = $this->getJson('/api/ai-model-datasets');

        $response->assertStatus(401);
    }

    /**
     * Test user can update AI model dataset link.
     */
    public function test_user_can_update_ai_model_dataset_link(): void
    {
        $payload = $this->validPayload();

        $createResponse = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        $datasetId = $createResponse->json('data.id');

        $updatePayload = [
            'rows_used' => 10000,
            'consent_check_status' => ConsentCheckStatus::WARNING->value,
            'business_justification' => 'Updated justification',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/ai-model-datasets/{$datasetId}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI model dataset link updated successfully',
            ])
            ->assertJsonPath('data.rows_used', 10000)
            ->assertJsonPath('data.consent_check_status', ConsentCheckStatus::WARNING->value)
            ->assertJsonPath('data.business_justification', 'Updated justification');

        $this->assertDatabaseHas('ai_model_dataset', [
            'id' => $datasetId,
            'rows_used' => 10000,
            'business_justification' => 'Updated justification',
        ]);
    }

    /**
     * Test user can partially update AI model dataset link.
     */
    public function test_user_can_partially_update_ai_model_dataset_link(): void
    {
        $payload = $this->validPayload();

        $createResponse = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        $datasetId = $createResponse->json('data.id');

        // Only update business_justification
        $updatePayload = [
            'business_justification' => 'Only justification updated',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/ai-model-datasets/{$datasetId}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('data.business_justification', 'Only justification updated')
            ->assertJsonPath('data.rows_used', $payload['rows_used']); // Original value unchanged
    }

    /**
     * Test updating dataset_snapshot_id requires it for specific roles.
     */
    public function test_updating_to_train_role_requires_snapshot_id(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        // Create with pretrain role (no snapshot required)
        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'role' => Role::PRETRAIN->value,
        ];

        $createResponse = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        $datasetId = $createResponse->json('data.id');

        // Try to update to train role without snapshot
        $updatePayload = [
            'role' => Role::TRAIN->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/ai-model-datasets/{$datasetId}", $updatePayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_snapshot_id']);
    }

    /**
     * Test update validates role enum.
     */
    public function test_update_validates_role_enum(): void
    {
        $payload = $this->validPayload();

        $createResponse = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        $datasetId = $createResponse->json('data.id');

        $updatePayload = [
            'role' => 'invalid_role',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/ai-model-datasets/{$datasetId}", $updatePayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /**
     * Test update validates consent_check_status enum.
     */
    public function test_update_validates_consent_check_status_enum(): void
    {
        $payload = $this->validPayload();

        $createResponse = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        $datasetId = $createResponse->json('data.id');

        $updatePayload = [
            'consent_check_status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/ai-model-datasets/{$datasetId}", $updatePayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['consent_check_status']);
    }

    /**
     * Test update validates cross_border_check enum.
     */
    public function test_update_validates_cross_border_check_enum(): void
    {
        $payload = $this->validPayload();

        $createResponse = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        $datasetId = $createResponse->json('data.id');

        $updatePayload = [
            'cross_border_check' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/ai-model-datasets/{$datasetId}", $updatePayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cross_border_check']);
    }

    /**
     * Test update validates linkage_status enum.
     */
    public function test_update_validates_linkage_status_enum(): void
    {
        $payload = $this->validPayload();

        $createResponse = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        $datasetId = $createResponse->json('data.id');

        $updatePayload = [
            'linkage_status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/ai-model-datasets/{$datasetId}", $updatePayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['linkage_status']);
    }

    /**
     * Test update validates max length constraints.
     */
    public function test_update_validates_max_length_constraints(): void
    {
        $payload = $this->validPayload();

        $createResponse = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);
        $datasetId = $createResponse->json('data.id');

        $updatePayload = [
            'training_duration' => str_repeat('a', 101),
        ];

        $response = $this->actingAs($this->user)->postJson("/api/ai-model-datasets/{$datasetId}", $updatePayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['training_duration']);
    }
}
