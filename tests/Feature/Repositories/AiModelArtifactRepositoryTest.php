<?php

namespace Tests\Feature\Repositories;

use App\Enums\ArtifactType;
use App\Models\AiModelArtifact;
use App\Models\AiModelVersion;
use App\Models\Organization;
use App\Models\User;
use App\Repositories\AiModelArtifactRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AiModelArtifactRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AiModelArtifactRepository $repository;
    private Organization $organization;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(AiModelArtifactRepository::class);
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_it_gets_paginated_artifacts_for_organization(): void
    {
        // Create artifacts for the test organization
        AiModelArtifact::factory()->count(10)->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create artifacts for a different organization
        $otherOrg = Organization::factory()->create();
        AiModelArtifact::factory()->count(5)->create([
            'organization_id' => $otherOrg->id,
        ]);

        $artifacts = $this->repository->getPaginatedArtifacts($this->organization->id, 15);

        $this->assertCount(10, $artifacts);
        $this->assertEquals(10, $artifacts->total());
        $this->assertInstanceOf(AiModelArtifact::class, $artifacts->first());
    }

    public function test_it_respects_per_page_parameter(): void
    {
        AiModelArtifact::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $artifacts = $this->repository->getPaginatedArtifacts($this->organization->id, 5);

        $this->assertCount(5, $artifacts);
        $this->assertEquals(20, $artifacts->total());
        $this->assertEquals(5, $artifacts->perPage());
    }

    public function test_it_returns_empty_collection_when_no_artifacts_exist(): void
    {
        $artifacts = $this->repository->getPaginatedArtifacts($this->organization->id, 15);

        $this->assertCount(0, $artifacts);
        $this->assertEquals(0, $artifacts->total());
    }

    public function test_it_filters_artifacts_by_organization_id(): void
    {
        $org1 = $this->organization;
        $org2 = Organization::factory()->create();
        $org3 = Organization::factory()->create();

        AiModelArtifact::factory()->count(3)->create(['organization_id' => $org1->id]);
        AiModelArtifact::factory()->count(5)->create(['organization_id' => $org2->id]);
        AiModelArtifact::factory()->count(2)->create(['organization_id' => $org3->id]);

        $org1Artifacts = $this->repository->getPaginatedArtifacts($org1->id);
        $org2Artifacts = $this->repository->getPaginatedArtifacts($org2->id);
        $org3Artifacts = $this->repository->getPaginatedArtifacts($org3->id);

        $this->assertEquals(3, $org1Artifacts->total());
        $this->assertEquals(5, $org2Artifacts->total());
        $this->assertEquals(2, $org3Artifacts->total());
    }

    public function test_it_can_create_ai_model_artifact(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::MODEL_BINARY,
            'uri' => 's3://ai-model-artifacts/models/test-model/model.bin',
            'checksum' => 'abc123def456',
            'size_bytes' => 1048576,
            'created_by' => $this->user->id,
            'notes' => 'Test model binary artifact',
        ];

        $artifact = $this->repository->createAiModelArtifact($data);

        $this->assertInstanceOf(AiModelArtifact::class, $artifact);
        $this->assertEquals($this->organization->id, $artifact->organization_id);
        $this->assertEquals($aiModelVersion->id, $artifact->ai_model_version_id);
        $this->assertEquals(ArtifactType::MODEL_BINARY, $artifact->artifact_type);
        $this->assertEquals('s3://ai-model-artifacts/models/test-model/model.bin', $artifact->uri);
        $this->assertEquals('abc123def456', $artifact->checksum);
        $this->assertEquals(1048576, $artifact->size_bytes);
        $this->assertEquals($this->user->id, $artifact->created_by);
        $this->assertEquals('Test model binary artifact', $artifact->notes);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'id' => $artifact->id,
            'organization_id' => $this->organization->id,
            'uri' => 's3://ai-model-artifacts/models/test-model/model.bin',
        ]);
    }

    public function test_it_can_create_artifact_with_minimal_data(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::CONFIG,
            'uri' => 's3://ai-model-artifacts/configs/test/config.yaml',
            'created_by' => $this->user->id,
        ];

        $artifact = $this->repository->createAiModelArtifact($data);

        $this->assertInstanceOf(AiModelArtifact::class, $artifact);
        $this->assertNull($artifact->checksum);
        $this->assertNull($artifact->size_bytes);
        $this->assertNull($artifact->notes);
    }

    public function test_it_can_create_artifacts_with_all_artifact_types(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        foreach (ArtifactType::cases() as $type) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_model_version_id' => $aiModelVersion->id,
                'artifact_type' => $type,
                'uri' => "s3://bucket/path/{$type->value}",
                'created_by' => $this->user->id,
            ];

            $artifact = $this->repository->createAiModelArtifact($data);

            $this->assertInstanceOf(AiModelArtifact::class, $artifact);
            $this->assertEquals($type, $artifact->artifact_type);
        }

        $this->assertDatabaseCount('ai_model_artifacts', count(ArtifactType::cases()));
    }

    public function test_it_can_create_docker_image_artifact(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::DOCKER_IMAGE,
            'uri' => 'docker://registry.example.com/ai-models/model-123:v1.0',
            'created_by' => $this->user->id,
        ];

        $artifact = $this->repository->createAiModelArtifact($data);

        $this->assertEquals(ArtifactType::DOCKER_IMAGE, $artifact->artifact_type);
        $this->assertEquals('docker://registry.example.com/ai-models/model-123:v1.0', $artifact->uri);
    }

    public function test_it_can_create_sbom_artifact(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::SBOM,
            'uri' => 's3://ai-model-artifacts/sbom/model-123/sbom.json',
            'checksum' => 'sha256:abcdef123456',
            'size_bytes' => 4096,
            'created_by' => $this->user->id,
            'notes' => 'Software Bill of Materials',
        ];

        $artifact = $this->repository->createAiModelArtifact($data);

        $this->assertEquals(ArtifactType::SBOM, $artifact->artifact_type);
        $this->assertEquals('sha256:abcdef123456', $artifact->checksum);
        $this->assertEquals('Software Bill of Materials', $artifact->notes);
    }

    public function test_it_can_update_ai_model_artifact(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'checksum' => 'old_checksum',
            'size_bytes' => 1024,
        ]);

        $updateData = [
            'checksum' => 'new_checksum_123',
            'size_bytes' => 2048,
            'notes' => 'Updated artifact notes',
        ];

        $result = $this->repository->updateAiModelArtifact($artifact, $updateData);

        $this->assertTrue($result);
        $artifact->refresh();
        $this->assertEquals('new_checksum_123', $artifact->checksum);
        $this->assertEquals(2048, $artifact->size_bytes);
        $this->assertEquals('Updated artifact notes', $artifact->notes);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'id' => $artifact->id,
            'checksum' => 'new_checksum_123',
            'size_bytes' => 2048,
            'notes' => 'Updated artifact notes',
        ]);
    }

    public function test_it_can_update_artifact_uri(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'uri' => 's3://old/path',
        ]);

        $updateData = [
            'uri' => 's3://new/path/to/artifact',
        ];

        $result = $this->repository->updateAiModelArtifact($artifact, $updateData);

        $this->assertTrue($result);
        $artifact->refresh();
        $this->assertEquals('s3://new/path/to/artifact', $artifact->uri);
    }

    public function test_it_can_clear_optional_fields_on_update(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'checksum' => 'some_checksum',
            'size_bytes' => 1024,
            'notes' => 'Some notes',
        ]);

        $updateData = [
            'checksum' => null,
            'size_bytes' => null,
            'notes' => null,
        ];

        $result = $this->repository->updateAiModelArtifact($artifact, $updateData);

        $this->assertTrue($result);
        $artifact->refresh();
        $this->assertNull($artifact->checksum);
        $this->assertNull($artifact->size_bytes);
        $this->assertNull($artifact->notes);
    }

    public function test_it_can_perform_partial_updates(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'checksum' => 'original_checksum',
            'size_bytes' => 1024,
            'notes' => 'Original notes',
        ]);

        $updateData = [
            'checksum' => 'updated_checksum',
        ];

        $result = $this->repository->updateAiModelArtifact($artifact, $updateData);

        $this->assertTrue($result);
        $artifact->refresh();
        $this->assertEquals('updated_checksum', $artifact->checksum);
        $this->assertEquals(1024, $artifact->size_bytes); // Unchanged
        $this->assertEquals('Original notes', $artifact->notes); // Unchanged
    }

    public function test_it_can_update_ai_model_version_id(): void
    {
        $aiModelVersion1 = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $aiModelVersion2 = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion1->id,
        ]);

        $updateData = [
            'ai_model_version_id' => $aiModelVersion2->id,
        ];

        $result = $this->repository->updateAiModelArtifact($artifact, $updateData);

        $this->assertTrue($result);
        $artifact->refresh();
        $this->assertEquals($aiModelVersion2->id, $artifact->ai_model_version_id);
    }

    public function test_update_returns_true_on_success(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->updateAiModelArtifact($artifact, [
            'notes' => 'Updated notes',
        ]);

        $this->assertTrue($result);
    }

    public function test_it_maintains_timestamps_on_update(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $originalCreatedAt = $artifact->created_at;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        $this->repository->updateAiModelArtifact($artifact, [
            'notes' => 'Updated notes',
        ]);

        $artifact->refresh();
        $this->assertEquals($originalCreatedAt->timestamp, $artifact->created_at->timestamp);
        $this->assertGreaterThan($artifact->created_at->timestamp, $artifact->updated_at->timestamp);
    }

    public function test_pagination_handles_large_datasets(): void
    {
        AiModelArtifact::factory()->count(100)->create([
            'organization_id' => $this->organization->id,
        ]);

        $page1 = $this->repository->getPaginatedArtifacts($this->organization->id, 25);
        $page2 = $this->repository->getPaginatedArtifacts($this->organization->id, 25);

        $this->assertEquals(100, $page1->total());
        $this->assertEquals(25, $page1->count());
        $this->assertEquals(4, $page1->lastPage());
    }

    public function test_it_creates_multiple_artifacts_for_same_version(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $artifact1 = $this->repository->createAiModelArtifact([
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::MODEL_BINARY,
            'uri' => 's3://bucket/model.bin',
            'created_by' => $this->user->id,
        ]);

        $artifact2 = $this->repository->createAiModelArtifact([
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::TOKENIZER,
            'uri' => 's3://bucket/tokenizer.json',
            'created_by' => $this->user->id,
        ]);

        $this->assertNotEquals($artifact1->id, $artifact2->id);
        $this->assertEquals($aiModelVersion->id, $artifact1->ai_model_version_id);
        $this->assertEquals($aiModelVersion->id, $artifact2->ai_model_version_id);
    }

    public function test_it_can_update_large_artifact(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'size_bytes' => 1024,
        ]);

        $updateData = [
            'size_bytes' => 10737418240, // 10GB
        ];

        $result = $this->repository->updateAiModelArtifact($artifact, $updateData);

        $this->assertTrue($result);
        $artifact->refresh();
        $this->assertEquals(10737418240, $artifact->size_bytes);
    }
}
