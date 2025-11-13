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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $artifacts = $this->repository->getFilteredAiArtifacts(['organization_id' => $this->organization->id, 'per_page' => 15]);

        $this->assertCount(10, $artifacts);
        $this->assertEquals(10, $artifacts->total());
        $this->assertInstanceOf(AiModelArtifact::class, $artifacts->first());
    }

    public function test_it_respects_per_page_parameter(): void
    {
        AiModelArtifact::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $artifacts = $this->repository->getFilteredAiArtifacts(['organization_id' => $this->organization->id, 'per_page' => 5]);

        $this->assertCount(5, $artifacts);
        $this->assertEquals(20, $artifacts->total());
        $this->assertEquals(5, $artifacts->perPage());
    }

    public function test_it_returns_empty_collection_when_no_artifacts_exist(): void
    {
        $artifacts = $this->repository->getFilteredAiArtifacts(['organization_id' => $this->organization->id, 'per_page' => 15]);

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

        $org1Artifacts = $this->repository->getFilteredAiArtifacts(['organization_id' => $org1->id]);
        $org2Artifacts = $this->repository->getFilteredAiArtifacts(['organization_id' => $org2->id]);
        $org3Artifacts = $this->repository->getFilteredAiArtifacts(['organization_id' => $org3->id]);

        $this->assertEquals(3, $org1Artifacts->total());
        $this->assertEquals(5, $org2Artifacts->total());
        $this->assertEquals(2, $org3Artifacts->total());
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

    public function test_create_ai_model_artifact(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $artifactData = [
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'name' => 'Test Artifact',
            'uri' => 's3://path/to/artifact',
            'checksum' => 'artifact_checksum_123',
            'size_bytes' => 4096,
            'artifact_type' => ArtifactType::DOCKER_IMAGE->value,
            'notes' => 'This is a test artifact',
            'created_by' => $this->user->email,
        ];

        $artifact = $this->repository->createAiModelArtifact($artifactData);

        $this->assertInstanceOf(AiModelArtifact::class, $artifact);
        $this->assertEquals($aiModelVersion->id, $artifact->ai_model_version_id);
        $this->assertEquals('Test Artifact', $artifact->name);
        $this->assertEquals('s3://path/to/artifact', $artifact->uri);
        $this->assertEquals('artifact_checksum_123', $artifact->checksum);
        $this->assertEquals(4096, $artifact->size_bytes);
        $this->assertEquals(ArtifactType::DOCKER_IMAGE->value, $artifact->artifact_type);
        $this->assertEquals('This is a test artifact', $artifact->notes);
        $this->assertEquals($this->user->email, $artifact->created_by);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'id' => $artifact->id,
            'name' => 'Test Artifact',
        ]);
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

    public function test_it_filters_by_artifact_type(): void
    {
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::TOKENIZER->value,
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::CONFIG->value,
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
        ];
        $result = $this->repository->getFilteredAiArtifacts($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $artifact) {
            $this->assertEquals(ArtifactType::MODEL_BINARY->value, $artifact->artifact_type);
        }
    }

    public function test_it_filters_by_name(): void
    {
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Production Model Binary',
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Development Tokenizer',
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Production Config File',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'name' => 'Production',
        ];
        $result = $this->repository->getFilteredAiArtifacts($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $artifact) {
            $this->assertStringContainsString('Production', $artifact->name);
        }
    }

    public function test_it_filters_by_name_case_insensitive(): void
    {
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'PRODUCTION Model Binary',
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'development tokenizer',
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Production Config',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'name' => 'production',
        ];
        $result = $this->repository->getFilteredAiArtifacts($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_partial_name_match(): void
    {
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'model-v1.0.0-binary',
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'tokenizer-config',
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'model-v2.0.0-binary',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'name' => 'v1',
        ];
        $result = $this->repository->getFilteredAiArtifacts($filters);

        $this->assertCount(1, $result->items());
        $this->assertStringContainsString('v1', $result->items()[0]->name);
    }

    public function test_it_filters_by_multiple_filters(): void
    {
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'name' => 'Production Model Binary v1',
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::TOKENIZER->value,
            'name' => 'Production Tokenizer',
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'name' => 'Development Model Binary',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'name' => 'Production',
        ];
        $result = $this->repository->getFilteredAiArtifacts($filters);

        $this->assertCount(1, $result->items());
        $artifact = $result->items()[0];
        $this->assertEquals(ArtifactType::MODEL_BINARY->value, $artifact->artifact_type);
        $this->assertStringContainsString('Production', $artifact->name);
    }

    public function test_it_filters_artifacts_by_different_types(): void
    {
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::DOCKER_IMAGE->value,
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::PROMPT_PACK->value,
        ]);
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::SBOM->value,
        ]);

        $dockerFilters = ['organization_id' => $this->organization->id, 'artifact_type' => ArtifactType::DOCKER_IMAGE->value];
        $promptFilters = ['organization_id' => $this->organization->id, 'artifact_type' => ArtifactType::PROMPT_PACK->value];
        $sbomFilters = ['organization_id' => $this->organization->id, 'artifact_type' => ArtifactType::SBOM->value];

        $dockerResult = $this->repository->getFilteredAiArtifacts($dockerFilters);
        $promptResult = $this->repository->getFilteredAiArtifacts($promptFilters);
        $sbomResult = $this->repository->getFilteredAiArtifacts($sbomFilters);

        $this->assertCount(1, $dockerResult->items());
        $this->assertCount(1, $promptResult->items());
        $this->assertCount(1, $sbomResult->items());
    }

    public function test_it_returns_empty_when_no_artifacts_match_filters(): void
    {
        AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'name' => 'Test Artifact',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::TOKENIZER->value,
            'name' => 'Nonexistent',
        ];
        $result = $this->repository->getFilteredAiArtifacts($filters);

        $this->assertCount(0, $result->items());
    }
}
