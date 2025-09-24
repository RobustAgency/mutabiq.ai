<?php

namespace Tests\Feature\Repositories;

use App\Models\AiModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\AiModelVersion;
use App\Repositories\AiModelVersionRepository;
use Tests\TestCase;

class AiModelVersionRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $aiModelVersionRepository;
    public function setUp(): void
    {
        parent::setUp();
        $this->aiModelVersionRepository = app(AiModelVersionRepository::class);
    }

    public function test_it_can_create_ai_model_version()
    {
        $aiModel = AiModel::factory()->create();
        $data = [
            'ai_model_id' => $aiModel->id,
            'version_number' => '1.0.0',
            'version_type' => 'major',
            'description' => 'Initial release',
            'release_notes' => 'First version of the AI model',
            'release_date' => now(),
            'architecture_type' => 'Transformer',
            'model_file_size_gb' => 2.5,
            'training_duration_hours' => 48,
            'complexity_level' => 'high',
            'parameter_count' => 1000000000,
            'input_modalities' => ['text', 'image'],
            'output_modalities' => ['text'],
            'deployment_status' => 'deployed',
            'lifecycle_stage' => 'production',
            'compliance_check_status' => 'compliant',
            'validation_status' => 'validated',
            'deployment_environments' => ['cloud', 'edge'],
            'rollback_available' => true,
            'has_performance_data' => true,
            'performance_baseline_established' => true,
        ];

        $aiModelVersion = $this->aiModelVersionRepository->create($data);

        $this->assertInstanceOf(AiModelVersion::class, $aiModelVersion);

        $this->assertDatabaseHas('ai_model_versions', [
            'id' => $aiModelVersion->id,
            'version_number' => '1.0.0',
        ]);
    }

    public function test_it_can_get_ai_model_version_by_id()
    {
        $aiModelVersion = AiModelVersion::factory()->create();

        $fetchedVersion = $this->aiModelVersionRepository->getAiModelVersionByID($aiModelVersion->id);

        $this->assertInstanceOf(AiModelVersion::class, $fetchedVersion);
        $this->assertEquals($aiModelVersion->id, $fetchedVersion->id);
    }

    public function test_it_can_update_ai_model_version()
    {
        $aiModelVersion = AiModelVersion::factory()->create();

        $updateData = [
            'description' => 'Updated description',
            'deployment_status' => 'not_deployed',
        ];

        $result = $this->aiModelVersionRepository->updateAiModelVersion($aiModelVersion, $updateData);

        $this->assertTrue($result);

        $this->assertDatabaseHas('ai_model_versions', [
            'id' => $aiModelVersion->id,
            'description' => 'Updated description',
            'deployment_status' => 'not_deployed',
        ]);
    }
}
