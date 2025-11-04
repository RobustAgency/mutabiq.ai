<?php

namespace Tests\Feature;

use App\Enums\ArtifactType;
use App\Models\AiModelArtifact;
use App\Models\AiModelVersion;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AiModelArtifactControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_index_returns_paginated_ai_model_artifacts(): void
    {
        AiModelArtifact::factory()->count(15)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-model-artifacts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'organization_id',
                            'ai_model_version_id',
                            'artifact_type',
                            'uri',
                            'checksum',
                            'size_bytes',
                            'created_by',
                            'notes',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ]
            ]);
    }

    public function test_index_returns_default_pagination(): void
    {
        AiModelArtifact::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-model-artifacts');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 15);
    }

    public function test_index_accepts_custom_per_page(): void
    {
        AiModelArtifact::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-model-artifacts?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 5)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_index_filters_by_organization(): void
    {
        // Create artifacts for current organization
        AiModelArtifact::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create artifacts for different organization
        $otherOrg = Organization::factory()->create();
        AiModelArtifact::factory()->count(5)->create([
            'organization_id' => $otherOrg->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-model-artifacts');

        $response->assertStatus(200)
            ->assertJsonPath('data.total', 3);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/ai-model-artifacts');

        $response->assertStatus(401);
    }

    public function test_store_creates_ai_model_artifact(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://ai-model-artifacts/models/test-model/model.bin,abc123def456,1048576,Test model binary artifact\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', [
                'file' => $file,
                'artifact_type' => ArtifactType::MODEL_BINARY->value,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Import completed successfully.',
            ]);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'uri' => 's3://ai-model-artifacts/models/test-model/model.bin',
            'checksum' => 'abc123def456',
            'size_bytes' => 1048576,
            'created_by' => $this->user->id,
            'notes' => 'Test model binary artifact',
        ]);
    }

    public function test_store_creates_artifact_with_all_artifact_types(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        foreach (ArtifactType::cases() as $type) {
            $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
            $csvContent .= "{$aiModelVersion->id},s3://bucket/path/to/artifact,checksum,1024,Note\n";

            $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-model-artifacts', [
                    'file' => $file,
                    'artifact_type' => $type->value,
                ]);

            $response->assertStatus(200);

            $this->assertDatabaseHas('ai_model_artifacts', [
                'artifact_type' => $type->value,
            ]);
        }
    }

    public function test_store_creates_artifact_without_optional_fields(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://ai-model-artifacts/configs/test/config.yaml,,,\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', [
                'file' => $file,
                'artifact_type' => ArtifactType::CONFIG->value,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::CONFIG->value,
            'uri' => 's3://ai-model-artifacts/configs/test/config.yaml',
            'checksum' => null,
            'size_bytes' => null,
            'notes' => null,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file', 'artifact_type']);
    }

    public function test_store_validates_artifact_type_is_valid(): void
    {
        $file = UploadedFile::fake()->create('artifacts.csv', 100);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', [
                'file' => $file,
                'artifact_type' => 'invalid_type',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['artifact_type']);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/ai-model-artifacts', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_ai_model_artifact(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/ai-model-artifacts/{$artifact->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Artifact retrieved successfully',
            ])
            ->assertJsonPath('data.id', $artifact->id)
            ->assertJsonPath('data.artifact_type', $artifact->artifact_type->value)
            ->assertJsonPath('data.uri', $artifact->uri);
    }

    public function test_show_requires_authentication(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson("/api/ai-model-artifacts/{$artifact->id}");

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_ai_model_artifact(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/ai-model-artifacts/{$artifact->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Artifact deleted successfully',
            ]);

        $this->assertDatabaseMissing('ai_model_artifacts', [
            'id' => $artifact->id,
        ]);
    }

    public function test_destroy_preserves_ai_model_version_when_deleting_artifact(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/ai-model-artifacts/{$artifact->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('ai_model_artifacts', ['id' => $artifact->id]);
        $this->assertDatabaseHas('ai_model_versions', ['id' => $aiModelVersion->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->deleteJson("/api/ai-model-artifacts/{$artifact->id}");

        $response->assertStatus(401);
    }

    public function test_store_creates_sbom_artifact(): void
    {
        Storage::fake('local');

        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $csvContent = "model_version_id,uri,checksum,size_bytes,notes\n";
        $csvContent .= "{$aiModelVersion->id},s3://ai-model-artifacts/sbom/model-123/sbom.json,sha256:abcdef123456,4096,\n";

        $file = UploadedFile::fake()->createWithContent('artifacts.csv', $csvContent);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', [
                'file' => $file,
                'artifact_type' => ArtifactType::SBOM->value,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'artifact_type' => ArtifactType::SBOM->value,
            'checksum' => 'sha256:abcdef123456',
        ]);
    }
}
