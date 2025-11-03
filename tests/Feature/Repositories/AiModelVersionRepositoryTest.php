<?php

namespace Tests\Feature\Repositories;

use App\Models\AiModel;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\AiModelVersion;
use App\Repositories\AiModelVersionRepository;
use Tests\TestCase;

class AiModelVersionRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $aiModelVersionRepository;
    private Organization $organization;

    public function setUp(): void
    {
        parent::setUp();
        $this->aiModelVersionRepository = app(AiModelVersionRepository::class);
        $this->organization = Organization::factory()->create();
    }

    public function test_it_gets_all_ai_model_versions(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);
        AiModelVersion::factory()->count(10)->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
        ]);

        $aiModelVersions = $this->aiModelVersionRepository->getFilteredAiModelVersions([
            'organization_id' => $this->organization->id,
            'ai_model_id' => $aiModel->id,
        ]);

        $this->assertCount(10, $aiModelVersions);
        $this->assertInstanceOf(AiModelVersion::class, $aiModelVersions->first());
    }

    public function test_it_gets_all_ai_model_versions_without_filter(): void
    {
        AiModelVersion::factory()->count(5)->create(['organization_id' => $this->organization->id]);
        AiModelVersion::factory()->count(3)->create(['organization_id' => $this->organization->id]);

        $aiModelVersions = $this->aiModelVersionRepository->getFilteredAiModelVersions([
            'organization_id' => $this->organization->id,
        ]);

        $this->assertCount(8, $aiModelVersions);
        $this->assertInstanceOf(AiModelVersion::class, $aiModelVersions->first());
    }

    public function test_it_can_create_ai_model_version(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);
        $data = [
            'organization_id' => $this->organization->id,
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

    public function test_it_can_get_ai_model_version_by_id(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create();

        $fetchedVersion = $this->aiModelVersionRepository->getAiModelVersionByID($aiModelVersion->id);

        $this->assertInstanceOf(AiModelVersion::class, $fetchedVersion);
        $this->assertEquals($aiModelVersion->id, $fetchedVersion->id);
    }

    public function test_it_can_update_ai_model_version(): void
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
