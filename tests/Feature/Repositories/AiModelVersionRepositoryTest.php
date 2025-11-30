<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Models\Organization;
use App\Models\AiModelVersion;
use App\Enums\VersionReleaseRole;
use Illuminate\Foundation\Testing\WithFaker;
use App\Repositories\AiModelVersionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiModelVersionRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $aiModelVersionRepository;

    private Organization $organization;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiModelVersionRepository = app(AiModelVersionRepository::class);
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
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
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
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

    public function test_it_filters_by_version_type(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'version_type' => 'major',
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'version_type' => 'minor',
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'version_type' => 'major',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'version_type' => 'major',
        ];
        $result = $this->aiModelVersionRepository->getFilteredAiModelVersions($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $version) {
            $this->assertEquals('major', $version->version_type);
        }
    }

    public function test_it_filters_by_deployment_status(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'deployment_status' => 'deployed',
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'deployment_status' => 'not_deployed',
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'deployment_status' => 'deployed',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'deployment_status' => 'deployed',
        ];
        $result = $this->aiModelVersionRepository->getFilteredAiModelVersions($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $version) {
            $this->assertEquals('deployed', $version->deployment_status);
        }
    }

    public function test_it_filters_by_lifecycle_stage(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'lifecycle_stage' => 'production',
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'lifecycle_stage' => 'development',
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'lifecycle_stage' => 'production',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'lifecycle_stage' => 'production',
        ];
        $result = $this->aiModelVersionRepository->getFilteredAiModelVersions($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $version) {
            $this->assertEquals('production', $version->lifecycle_stage);
        }
    }

    public function test_it_filters_by_release_role(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'release_role' => VersionReleaseRole::ORIGINAL_RELEASE,
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'release_role' => VersionReleaseRole::PATCH,
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'release_role' => VersionReleaseRole::ORIGINAL_RELEASE,
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'release_role' => VersionReleaseRole::ORIGINAL_RELEASE,
        ];
        $result = $this->aiModelVersionRepository->getFilteredAiModelVersions($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_date_range(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(10),
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(5),
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'from' => now()->subDays(7)->format('Y-m-d'),
            'to' => now()->subDays(2)->format('Y-m-d'),
        ];
        $result = $this->aiModelVersionRepository->getFilteredAiModelVersions($filters);

        $this->assertCount(1, $result->items());
    }

    public function test_it_filters_by_from_date_only(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(10),
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(5),
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'from' => now()->subDays(6)->format('Y-m-d'),
        ];
        $result = $this->aiModelVersionRepository->getFilteredAiModelVersions($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_to_date_only(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(10),
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(5),
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'to' => now()->subDays(6)->format('Y-m-d'),
        ];
        $result = $this->aiModelVersionRepository->getFilteredAiModelVersions($filters);

        $this->assertCount(1, $result->items());
    }

    public function test_it_filters_by_multiple_filters(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'version_type' => 'major',
            'deployment_status' => 'deployed',
            'lifecycle_stage' => 'production',
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'version_type' => 'minor',
            'deployment_status' => 'deployed',
            'lifecycle_stage' => 'production',
        ]);
        AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
            'version_type' => 'major',
            'deployment_status' => 'not_deployed',
            'lifecycle_stage' => 'development',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'version_type' => 'major',
            'deployment_status' => 'deployed',
            'lifecycle_stage' => 'production',
        ];
        $result = $this->aiModelVersionRepository->getFilteredAiModelVersions($filters);

        $this->assertCount(1, $result->items());
        $version = $result->items()[0];
        $this->assertEquals('major', $version->version_type);
        $this->assertEquals('deployed', $version->deployment_status);
        $this->assertEquals('production', $version->lifecycle_stage);
    }

    public function test_it_respects_per_page_parameter(): void
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);

        AiModelVersion::factory()->count(20)->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'per_page' => 5,
        ];
        $result = $this->aiModelVersionRepository->getFilteredAiModelVersions($filters);

        $this->assertCount(5, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(5, $result->perPage());
    }
}
