<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\VersionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\Organization;
use App\Models\User;
use App\Enums\ComplexityLevel;
use App\Enums\ComplianceStatus;
use App\Enums\DeploymentStatus;
use App\Enums\LifecycleStage;
use App\Enums\ValidationStatus;

class AiModelVersionControllerTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
    }

    public function test_user_can_get_all_ai_model_versions(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);
        AiModelVersion::factory()->count(5)->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
        ]);
        $url = '/api/ai-model-versions?ai_model_id=' . $aiModel->id;

        $response = $this->actingAs($user)->getJson($url);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'ai_model_id',
                        'version_number',
                        'version_type',
                        'description',
                        'release_notes',
                        'release_date',
                        'architecture_type',
                        'model_file_size_gb',
                        'training_duration_hours',
                        'complexity_level',
                        'deployment_status',
                        'lifecycle_stage',
                        'parameter_count',
                        'input_modalities',
                        'output_modalities',
                        'deployment_environments',
                        'has_performance_data',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_user_can_create_ai_model_version(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        $data = [
            'ai_model_id' => $aiModel->id,
            'version_number' => '1.0.0',
            'version_type' => VersionType::MAJOR,
            'description' => 'Initial release',
            'release_notes' => 'First version of the AI model',
            'release_date' => now()->toDateString(),
            'architecture_type' => 'Transformer',
            'model_file_size_gb' => 2.5,
            'training_duration_hours' => 48,
            'complexity_level' => ComplexityLevel::MODERATE,
            'parameter_count' => 1000000000,
            'input_modalities' => ['text', 'image'],
            'output_modalities' => ['text'],
            'deployment_status' => DeploymentStatus::NOT_DEPLOYED,
            'lifecycle_stage' => LifecycleStage::DEVELOPMENT,
            'compliance_check_status' => ComplianceStatus::NOT_CHECKED,
            'validation_status' => ValidationStatus::IN_PROGRESS,
            'deployment_environments' => ['cloud', 'edge'],
        ];

        $response = $this->actingAs($user)->postJson('/api/ai-model-versions', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('ai_model_versions', [
            'version_number' => '1.0.0',
            'ai_model_id' => $aiModel->id,
        ]);
    }

    public function test_user_can_update_ai_model_version(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = [
            'version_number' => '1.0.1',
            'description' => 'Updated description',
            'deployment_status' => DeploymentStatus::DEPLOYED,
        ];

        $response = $this->actingAs($user)->postJson("/api/ai-model-versions/{$aiModelVersion->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ai_model_versions', [
            'id' => $aiModelVersion->id,
            'version_number' => '1.0.1',
            'description' => 'Updated description',
            'deployment_status' => DeploymentStatus::DEPLOYED,
        ]);
    }

    public function test_user_can_get_ai_model_version(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($user)->getJson("/api/ai-model-versions/{$aiModelVersion->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'version',
                    'version_type',
                    'description',
                    'release_notes',
                    'release_date',
                    'architecture_type',
                    'model_file_size_gb',
                    'training_duration_hours',
                    'complexity',
                    'deployment_status',
                    'lifecycle_stage',
                    'validation_status',
                    'compliance_status',
                    'parameter_count',
                    'input_modalities',
                    'output_modalities',
                    'deployment_environments',
                    'rollback_available',
                    'has_performance_data',
                    'performance_baseline_established',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_it_handles_validation_errors_on_create(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);

        // Missing required fields
        $data = [
            'ai_model_id' => null,
            'version_number' => '',
            'version_type' => 'invalid_enum_value',
        ];

        $response = $this->actingAs($user)->postJson('/api/ai-model-versions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_model_id', 'version_number', 'version_type']);
    }

    public function test_it_handles_validation_errors_on_update(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['organization_id' => $this->organization->id]);

        // Invalid enum value
        $updateData = [
            'version_type' => 'invalid_enum_value',
            'deployment_status' => 'invalid_enum_value',
        ];

        $response = $this->actingAs($user)->postJson("/api/ai-model-versions/{$aiModelVersion->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['version_type', 'deployment_status']);
    }
}
