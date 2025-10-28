<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\AiModelDataset\EligibilityStatus;
use App\Enums\AiModelDataset\Role;
use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiModelDatasetControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();
        $snapshot = DatasetSnapshot::factory()->create(['dataset_id' => $dataset->id]);

        return array_merge([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TRAIN->value,
            'access_path' => '/data/training/path',
            'transform_pack_link' => 'https://transforms.example.com/pack123',
            'license_check_ref' => 'LIC-123456',
            'privacy_check_ref' => 'PRI-789012',
            'eligibility_status' => EligibilityStatus::ELIGIBLE->value,
            'notes' => 'Test dataset assignment',
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
                    'ai_model_id',
                    'ai_model_version_id',
                    'dataset_id',
                    'dataset_snapshot_id',
                    'role',
                    'access_path',
                    'transform_pack_link',
                    'license_check_ref',
                    'privacy_check_ref',
                    'eligibility_status',
                    'notes',
                    'created_at',
                    'updated_at',
                ]
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

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'role' => Role::PRETRAIN->value,
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

        foreach (Role::cases() as $role) {
            $payload = [
                'ai_model_id' => $aiModel->id,
                'ai_model_version_id' => $aiModelVersion->id,
                'role' => $role->value,
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

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'role' => Role::PRETRAIN->value,
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

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'role' => Role::FINE_TUNE->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(201);
    }

    /**
     * Test eligibility_status must be valid enum if provided.
     */
    public function test_eligibility_status_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['eligibility_status' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['eligibility_status']);
    }

    /**
     * Test all eligibility status enum values are accepted.
     */
    public function test_all_eligibility_status_enum_values_are_accepted(): void
    {
        foreach (EligibilityStatus::cases() as $status) {
            $payload = $this->validPayload(['eligibility_status' => $status->value]);

            $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test access_path max length is 500.
     */
    public function test_access_path_max_length_is_500(): void
    {
        $payload = $this->validPayload(['access_path' => str_repeat('a', 501)]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['access_path']);
    }

    /**
     * Test transform_pack_link max length is 500.
     */
    public function test_transform_pack_link_max_length_is_500(): void
    {
        $payload = $this->validPayload(['transform_pack_link' => str_repeat('a', 501)]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['transform_pack_link']);
    }

    /**
     * Test license_check_ref max length is 255.
     */
    public function test_license_check_ref_max_length_is_255(): void
    {
        $payload = $this->validPayload(['license_check_ref' => str_repeat('a', 256)]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['license_check_ref']);
    }

    /**
     * Test privacy_check_ref max length is 255.
     */
    public function test_privacy_check_ref_max_length_is_255(): void
    {
        $payload = $this->validPayload(['privacy_check_ref' => str_repeat('a', 256)]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['privacy_check_ref']);
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
     * Test nullable fields can be omitted.
     */
    public function test_nullable_fields_can_be_omitted(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $snapshot = DatasetSnapshot::factory()->create();

        $payload = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TRAIN->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(201);
    }

    /**
     * Test link with eligible_with_conditions status.
     */
    public function test_link_with_eligible_with_conditions_status(): void
    {
        $payload = $this->validPayload([
            'eligibility_status' => EligibilityStatus::ELIGIBLE_WITH_CONDITIONS->value,
            'notes' => 'Requires additional privacy review.',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.eligibility_status', EligibilityStatus::ELIGIBLE_WITH_CONDITIONS->value)
            ->assertJsonPath('data.notes', 'Requires additional privacy review.');
    }

    /**
     * Test link with not_eligible status.
     */
    public function test_link_with_not_eligible_status(): void
    {
        $payload = $this->validPayload([
            'eligibility_status' => EligibilityStatus::NOT_ELIGIBLE->value,
            'notes' => 'License restrictions prevent usage.',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/ai-model-datasets', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.eligibility_status', EligibilityStatus::NOT_ELIGIBLE->value)
            ->assertJsonPath('data.notes', 'License restrictions prevent usage.');
    }
}
