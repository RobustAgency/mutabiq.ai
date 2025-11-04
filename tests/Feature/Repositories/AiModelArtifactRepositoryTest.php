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

    public function test_it_can_create_ai_model_artifact_from_csv(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/model.bin,checksum1,1024,Test note\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $data = [
            'file' => $file,
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'created_by' => $this->user->id,
        ];

        $result = $this->repository->createAiModelArtifact($data);

        $this->assertIsArray($result);
        $this->assertFalse($result['error']);
        $this->assertEquals('Import completed successfully', $result['message']);
        $this->assertEquals(1, $result['data']['successful']);
        $this->assertEquals(0, $result['data']['failed']);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'uri' => 's3://bucket/model.bin',
            'checksum' => 'checksum1',
            'size_bytes' => 1024,
            'notes' => 'Test note',
        ]);
    }

    public function test_it_can_create_artifact_with_minimal_data(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/config.yaml,,,\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $data = [
            'file' => $file,
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::CONFIG->value,
            'created_by' => $this->user->id,
        ];

        $result = $this->repository->createAiModelArtifact($data);

        $this->assertIsArray($result);
        $this->assertFalse($result['error']);
        $this->assertEquals(1, $result['data']['successful']);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'organization_id' => $this->organization->id,
            'uri' => 's3://bucket/config.yaml',
            'checksum' => null,
            'size_bytes' => null,
            'notes' => null,
        ]);
    }

    public function test_it_can_create_artifacts_with_all_artifact_types(): void
    {
        Storage::fake('local');

        foreach (ArtifactType::cases() as $type) {
            $aiModelVersion = AiModelVersion::factory()->create([
                'organization_id' => $this->organization->id,
            ]);

            $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
            $csvContent .= "{$aiModelVersion->id},s3://bucket/path/{$type->value},checksum,1024,Note\n";

            $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

            $data = [
                'file' => $file,
                'organization_id' => $this->organization->id,
                'artifact_type' => $type->value,
                'created_by' => $this->user->id,
            ];

            $result = $this->repository->createAiModelArtifact($data);

            $this->assertFalse($result['error'], "Failed to import artifact type: {$type->value}");
            $this->assertEquals(1, $result['data']['successful']);

            $this->assertDatabaseHas('ai_model_artifacts', [
                'artifact_type' => $type->value,
            ]);
        }

        $this->assertDatabaseCount('ai_model_artifacts', count(ArtifactType::cases()));
    }

    public function test_it_can_create_docker_image_artifact(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},docker://registry.example.com/ai-models/model-123:v1.0,checksum,1024,Note\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $data = [
            'file' => $file,
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::DOCKER_IMAGE->value,
            'created_by' => $this->user->id,
        ];

        $result = $this->repository->createAiModelArtifact($data);

        $this->assertFalse($result['error']);
        $this->assertEquals(1, $result['data']['successful']);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'artifact_type' => ArtifactType::DOCKER_IMAGE->value,
            'uri' => 'docker://registry.example.com/ai-models/model-123:v1.0',
        ]);
    }

    public function test_it_can_create_sbom_artifact(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/sbom/model-123/sbom.json,sha256:abcdef123456,4096,Software Bill of Materials\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $data = [
            'file' => $file,
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::SBOM->value,
            'created_by' => $this->user->id,
        ];

        $result = $this->repository->createAiModelArtifact($data);

        $this->assertFalse($result['error']);
        $this->assertEquals(1, $result['data']['successful']);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'artifact_type' => ArtifactType::SBOM->value,
            'checksum' => 'sha256:abcdef123456',
            'notes' => 'Software Bill of Materials',
        ]);
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

    public function test_it_creates_multiple_artifacts_from_csv(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/model1.bin,checksum1,1024,Note 1\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/model2.bin,checksum2,2048,Note 2\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $data = [
            'file' => $file,
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'created_by' => $this->user->id,
        ];

        $result = $this->repository->createAiModelArtifact($data);

        $this->assertFalse($result['error']);
        $this->assertEquals(2, $result['data']['successful']);
        $this->assertEquals(0, $result['data']['failed']);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'uri' => 's3://bucket/model1.bin',
        ]);
        $this->assertDatabaseHas('ai_model_artifacts', [
            'uri' => 's3://bucket/model2.bin',
        ]);
    }

    public function test_it_handles_large_batch_imports_via_csv(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        for ($i = 1; $i <= 50; $i++) {
            $csvContent .= "{$aiModelVersion->id},s3://bucket/model{$i}.bin,checksum{$i},{$i}000,Note {$i}\n";
        }

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $data = [
            'file' => $file,
            'organization_id' => $this->organization->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'created_by' => $this->user->id,
        ];

        $result = $this->repository->createAiModelArtifact($data);

        $this->assertFalse($result['error']);
        $this->assertEquals(50, $result['data']['successful']);
        $this->assertEquals(0, $result['data']['failed']);
        $this->assertCount(50, \App\Models\AiModelArtifact::all());
    }
}
