<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\ArtifactType;
use App\Models\Organization;
use App\Models\AiModelVersion;
use App\Models\AiModelArtifact;
use App\Enums\ArtifactChecksumAlgorithm;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
                            'checksum_algorithm',
                            'checksum_value',
                            'file',
                            'environment',
                            'file_format',
                            'size_bytes',
                            'created_by',
                            'notes',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
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
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', [
                'ai_model_version_id' => $aiModelVersion->id,
                'name' => 'test-model.bin',
                'uri' => 'https://s3.amazonaws.com/ai-model-artifacts/models/test-model/model.bin',
                'checksum_algorithm' => ArtifactChecksumAlgorithm::MD5->value,
                'environment' => 'production',
                'artifact_type' => ArtifactType::DOCUMENTATION->value,
                'notes' => 'Test model binary artifact',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Artifact created successfully',
            ]);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'organization_id' => $this->organization->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::DOCUMENTATION->value,
            'uri' => 'https://s3.amazonaws.com/ai-model-artifacts/models/test-model/model.bin',
            'checksum_algorithm' => ArtifactChecksumAlgorithm::MD5->value,
            'notes' => 'Test model binary artifact',
        ]);
    }

    public function test_store_creates_artifact_with_all_artifact_types(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        foreach (ArtifactType::cases() as $type) {
            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-model-artifacts', [
                    'ai_model_version_id' => $aiModelVersion->id,
                    'name' => 'artifact-'.$type->value,
                    'uri' => 'https://s3.amazonaws.com/bucket/path/to/artifact',
                    'artifact_type' => $type->value,
                    'notes' => 'Test note',
                ]);

            $response->assertStatus(201);

            $this->assertDatabaseHas('ai_model_artifacts', [
                'artifact_type' => $type->value,
            ]);
        }
    }

    public function test_store_creates_artifact_without_optional_fields(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', [
                'ai_model_version_id' => $aiModelVersion->id,
                'name' => 'config.yaml',
                'uri' => 'https://s3.amazonaws.com/ai-model-artifacts/configs/test/config.yaml',
                'artifact_type' => ArtifactType::CONFIG_FILE->value,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::CONFIG_FILE->value,
            'uri' => 'https://s3.amazonaws.com/ai-model-artifacts/configs/test/config.yaml',
            'notes' => null,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'ai_model_version_id',
                'name',
                'artifact_type',
            ]);
    }

    public function test_store_validates_artifact_type_is_valid(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', [
                'ai_model_version_id' => $aiModelVersion->id,
                'name' => 'test-artifact',
                'uri' => 'https://example.com/artifact',
                'artifact_type' => 'invalid_type',
            ]);

        $response->assertStatus(422);
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
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', [
                'ai_model_version_id' => $aiModelVersion->id,
                'name' => 'sbom.json',
                'uri' => 'https://s3.amazonaws.com/ai-model-artifacts/sbom/model-123/sbom.json',
                'checksum_algorithm' => 'sha256',
                'artifact_type' => ArtifactType::CONTAINER_IMAGE->value,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'artifact_type' => ArtifactType::CONTAINER_IMAGE->value,
            'checksum_algorithm' => 'sha256',
        ]);
    }
}
