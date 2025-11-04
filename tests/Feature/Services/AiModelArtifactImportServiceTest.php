<?php

namespace Tests\Feature\Services;

use App\Enums\ArtifactType;
use App\Models\AiModelVersion;
use App\Models\Organization;
use App\Models\User;
use App\Services\AiModelArtifactImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AiModelArtifactImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiModelArtifactImportService $service;
    private Organization $organization;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AiModelArtifactImportService::class);
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_imports_artifacts_from_csv_file(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/model.bin,checksum1,1024,Test note\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::MODEL_BINARY->value,
            $this->user->id
        );

        $this->assertFalse($result['error']);
        $this->assertEquals('Import completed successfully.', $result['message']);
        $this->assertEquals(1, $result['data']['successful']);
        $this->assertEquals(0, $result['data']['failed']);
        $this->assertEmpty($result['data']['errors']);

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

    public function test_imports_multiple_artifacts(): void
    {
        Storage::fake('local');

        $aiModelVersion1 = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $aiModelVersion2 = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion1->id},s3://bucket/model1.bin,checksum1,1024,Note 1\n";
        $csvContent .= "{$aiModelVersion2->id},s3://bucket/model2.bin,checksum2,2048,Note 2\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::TOKENIZER->value,
            $this->user->id
        );

        $this->assertFalse($result['error']);
        $this->assertEquals(2, $result['data']['successful']);
        $this->assertEquals(0, $result['data']['failed']);
        $this->assertCount(2, \App\Models\AiModelArtifact::all());
    }

    public function test_imports_artifacts_with_nullable_fields(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/config.yaml,,,\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::CONFIG->value,
            $this->user->id
        );

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

    public function test_handles_validation_errors(): void
    {
        Storage::fake('local');

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "99999,s3://bucket/model.bin,checksum1,1024,Note\n"; // Invalid version ID

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::MODEL_BINARY->value,
            $this->user->id
        );

        $this->assertTrue($result['error']);
        $this->assertEquals(0, $result['data']['successful']);
        $this->assertEquals(1, $result['data']['failed']);
        $this->assertNotEmpty($result['data']['errors']);
    }

    public function test_continues_on_row_errors(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "99999,s3://bucket/invalid.bin,checksum1,1024,Note 1\n"; // Invalid
        $csvContent .= "{$aiModelVersion->id},s3://bucket/valid.bin,checksum2,2048,Note 2\n"; // Valid
        $csvContent .= ",s3://bucket/invalid2.bin,checksum3,3072,Note 3\n"; // Invalid - missing version

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::MODEL_BINARY->value,
            $this->user->id
        );

        $this->assertTrue($result['error']);
        $this->assertEquals(1, $result['data']['successful']);
        $this->assertEquals(2, $result['data']['failed']);
        $this->assertCount(2, $result['data']['errors']);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'uri' => 's3://bucket/valid.bin',
        ]);
    }

    public function test_handles_general_exceptions(): void
    {
        Storage::fake('local');

        // Create an invalid file that will cause an exception
        $file = UploadedFile::fake()->create('corrupted.csv', 100);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::MODEL_BINARY->value,
            $this->user->id
        );

        $this->assertTrue($result['error']);
        $this->assertStringContainsString('Import failed:', $result['message']);
        $this->assertEquals(0, $result['data']['successful']);
        $this->assertEquals(0, $result['data']['failed']);
        $this->assertNotEmpty($result['data']['errors']);
    }

    public function test_imports_all_artifact_types(): void
    {
        Storage::fake('local');

        foreach (ArtifactType::cases() as $type) {
            $aiModelVersion = AiModelVersion::factory()->create([
                'organization_id' => $this->organization->id,
            ]);

            $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
            $csvContent .= "{$aiModelVersion->id},s3://bucket/{$type->value}.bin,checksum,1024,Note\n";

            $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

            $result = $this->service->import(
                $file,
                $this->organization->id,
                $type->value,
                $this->user->id
            );

            $this->assertFalse($result['error'], "Failed to import artifact type: {$type->value}");
            $this->assertDatabaseHas('ai_model_artifacts', [
                'artifact_type' => $type->value,
            ]);
        }
    }

    public function test_handles_large_batch_imports(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        for ($i = 1; $i <= 100; $i++) {
            $csvContent .= "{$aiModelVersion->id},s3://bucket/model{$i}.bin,checksum{$i},{$i}000,Note {$i}\n";
        }

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::MODEL_BINARY->value,
            $this->user->id
        );

        $this->assertFalse($result['error']);
        $this->assertEquals(100, $result['data']['successful']);
        $this->assertEquals(0, $result['data']['failed']);
        $this->assertCount(100, \App\Models\AiModelArtifact::all());
    }

    public function test_validates_uri_max_length(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $longUri = 's3://' . str_repeat('a', 1020); // Exceeds 1024 limit
        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},{$longUri},checksum,1024,Note\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::MODEL_BINARY->value,
            $this->user->id
        );

        $this->assertTrue($result['error']);
        $this->assertEquals(0, $result['data']['successful']);
        $this->assertEquals(1, $result['data']['failed']);
    }

    public function test_validates_negative_size_bytes(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/model.bin,checksum,-100,Note\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::MODEL_BINARY->value,
            $this->user->id
        );

        $this->assertTrue($result['error']);
        $this->assertEquals(0, $result['data']['successful']);
        $this->assertEquals(1, $result['data']['failed']);
    }

    public function test_provides_row_numbers_in_error_messages(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/valid.bin,checksum,1024,Note\n"; // Row 2 - Valid
        $csvContent .= "99999,s3://bucket/invalid.bin,checksum,1024,Note\n"; // Row 3 - Invalid

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::MODEL_BINARY->value,
            $this->user->id
        );

        $this->assertTrue($result['error']);
        $this->assertEquals(1, $result['data']['successful']);
        $this->assertEquals(1, $result['data']['failed']);
        $this->assertArrayHasKey('row', $result['data']['errors'][0]);
        $this->assertEquals(3, $result['data']['errors'][0]['row']);
    }

    public function test_returns_correct_response_structure(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://bucket/model.bin,checksum,1024,Note\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $result = $this->service->import(
            $file,
            $this->organization->id,
            ArtifactType::MODEL_BINARY->value,
            $this->user->id
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total_processed', $result['data']);
        $this->assertArrayHasKey('successful', $result['data']);
        $this->assertArrayHasKey('failed', $result['data']);
        $this->assertArrayHasKey('errors', $result['data']);
    }
}
