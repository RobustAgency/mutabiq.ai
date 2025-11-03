<?php

namespace Tests\Feature;

use App\Enums\ArtifactType;
use App\Models\AiModelArtifact;
use App\Models\AiModelVersion;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'uri' => 's3://ai-model-artifacts/models/test-model/model.bin',
            'checksum' => 'abc123def456',
            'size_bytes' => 1048576, // 1MB
            'notes' => 'Test model binary artifact',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Artifact created successfully',
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
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        foreach (ArtifactType::cases() as $type) {
            $data = [
                'ai_model_version_id' => $aiModelVersion->id,
                'artifact_type' => $type->value,
                'uri' => 's3://bucket/path/to/artifact',
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/ai-model-artifacts', $data);

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

        $data = [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::CONFIG->value,
            'uri' => 's3://ai-model-artifacts/configs/test/config.yaml',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(201);

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
        $data = [];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_model_version_id', 'artifact_type', 'uri']);
    }

    public function test_store_validates_ai_model_version_exists(): void
    {
        $data = [
            'ai_model_version_id' => 99999,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'uri' => 's3://bucket/path',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_model_version_id']);
    }

    public function test_store_validates_artifact_type_is_valid(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => 'invalid_type',
            'uri' => 's3://bucket/path',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['artifact_type']);
    }

    public function test_store_validates_uri_max_length(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'uri' => str_repeat('a', 1025), // Exceeds max length
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['uri']);
    }

    public function test_store_validates_size_bytes_is_positive(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'uri' => 's3://bucket/path',
            'size_bytes' => -100,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['size_bytes']);
    }

    public function test_store_validates_notes_max_length(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::MODEL_BINARY->value,
            'uri' => 's3://bucket/path',
            'notes' => str_repeat('a', 2001), // Exceeds max length
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
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

    public function test_show_returns_404_for_non_existent_artifact(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-model-artifacts/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson("/api/ai-model-artifacts/{$artifact->id}");

        $response->assertStatus(401);
    }

    public function test_update_modifies_ai_model_artifact(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'checksum' => 'old_checksum',
        ]);

        $updateData = [
            'artifact_type' => ArtifactType::TOKENIZER->value,
            'checksum' => 'new_checksum_123',
            'size_bytes' => 2048576,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-model-artifacts/{$artifact->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Artifact updated successfully',
            ]);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'id' => $artifact->id,
            'artifact_type' => ArtifactType::TOKENIZER->value,
            'checksum' => 'new_checksum_123',
            'size_bytes' => 2048576,
        ]);
    }

    public function test_update_can_change_uri(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'uri' => 's3://old/path',
        ]);

        $updateData = [
            'artifact_type' => $artifact->artifact_type->value,
            'uri' => 's3://new/path/to/artifact',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-model-artifacts/{$artifact->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'id' => $artifact->id,
            'uri' => 's3://new/path/to/artifact',
        ]);
    }

    public function test_update_can_clear_optional_fields(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'checksum' => 'some_checksum',
            'size_bytes' => 1024,
            'notes' => 'Some notes',
        ]);

        $updateData = [
            'artifact_type' => $artifact->artifact_type->value,
            'checksum' => null,
            'size_bytes' => null,
            'notes' => null,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-model-artifacts/{$artifact->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'id' => $artifact->id,
            'checksum' => null,
            'size_bytes' => null,
            'notes' => null,
        ]);
    }

    public function test_update_validates_ai_model_version_exists(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $updateData = [
            'ai_model_version_id' => 99999,
            'artifact_type' => $artifact->artifact_type->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-model-artifacts/{$artifact->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_model_version_id']);
    }

    public function test_update_validates_artifact_type_is_valid(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $updateData = [
            'artifact_type' => 'invalid_type',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-model-artifacts/{$artifact->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['artifact_type']);
    }

    public function test_update_supports_partial_updates(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
            'checksum' => 'old_checksum',
            'size_bytes' => 1024,
        ]);

        $updateData = [
            'artifact_type' => $artifact->artifact_type->value,
            'checksum' => 'updated_checksum',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-model-artifacts/{$artifact->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'id' => $artifact->id,
            'checksum' => 'updated_checksum',
            'size_bytes' => 1024, // Unchanged
        ]);
    }

    public function test_update_requires_authentication(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/ai-model-artifacts/{$artifact->id}", []);

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

    public function test_destroy_returns_404_for_non_existent_artifact(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/ai-model-artifacts/99999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $artifact = AiModelArtifact::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->deleteJson("/api/ai-model-artifacts/{$artifact->id}");

        $response->assertStatus(401);
    }

    public function test_store_creates_docker_image_artifact(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::DOCKER_IMAGE->value,
            'uri' => 'docker://registry.example.com/ai-models/model-123:v1.0',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'artifact_type' => ArtifactType::DOCKER_IMAGE->value,
            'uri' => 'docker://registry.example.com/ai-models/model-123:v1.0',
        ]);
    }

    public function test_store_creates_sbom_artifact(): void
    {
        $aiModelVersion = AiModelVersion::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'ai_model_version_id' => $aiModelVersion->id,
            'artifact_type' => ArtifactType::SBOM->value,
            'uri' => 's3://ai-model-artifacts/sbom/model-123/sbom.json',
            'checksum' => 'sha256:abcdef123456',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-model-artifacts', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('ai_model_artifacts', [
            'artifact_type' => ArtifactType::SBOM->value,
            'checksum' => 'sha256:abcdef123456',
        ]);
    }
}
