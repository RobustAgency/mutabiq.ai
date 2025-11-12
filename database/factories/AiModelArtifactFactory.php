<?php

namespace Database\Factories;

use App\Enums\ArtifactType;
use App\Models\AiModelArtifact;
use App\Models\AiModelVersion;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiModelArtifact>
 */
class AiModelArtifactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AiModelArtifact>
     */
    protected $model = AiModelArtifact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $artifactType = fake()->randomElement(ArtifactType::cases());

        return [
            'organization_id' => Organization::factory(),
            'ai_model_version_id' => AiModelVersion::factory(),
            'name' => fake()->words(5, true),
            'artifact_type' => $artifactType,
            'uri' => $this->generateUri($artifactType),
            'checksum' => fake()->sha256(),
            'size_bytes' => fake()->numberBetween(1024, 10737418240), // 1KB to 10GB
            'created_by' => User::factory(),
            'notes' => fake()->optional(0.7)->sentence(12),
        ];
    }

    /**
     * Generate a URI based on artifact type.
     */
    private function generateUri(ArtifactType $type): string
    {
        $bucket = 's3://ai-model-artifacts';
        $modelId = fake()->uuid();

        return match ($type) {
            ArtifactType::MODEL_BINARY => "{$bucket}/models/{$modelId}/model.bin",
            ArtifactType::TOKENIZER => "{$bucket}/tokenizers/{$modelId}/tokenizer.json",
            ArtifactType::PROMPT_PACK => "{$bucket}/prompts/{$modelId}/prompts.zip",
            ArtifactType::INDEX => "{$bucket}/indices/{$modelId}/index.faiss",
            ArtifactType::FEATURE_STORE_EXPORT => "{$bucket}/features/{$modelId}/features.parquet",
            ArtifactType::CONFIG => "{$bucket}/configs/{$modelId}/config.yaml",
            ArtifactType::DOCKER_IMAGE => "docker://registry.example.com/ai-models/{$modelId}:latest",
            ArtifactType::SBOM => "{$bucket}/sbom/{$modelId}/sbom.json",
        };
    }

    /**
     * Indicate that the artifact is a model binary.
     */
    public function modelBinary(): static
    {
        return $this->state(fn(array $attributes) => [
            'artifact_type' => ArtifactType::MODEL_BINARY,
            'uri' => 's3://ai-model-artifacts/models/' . fake()->uuid() . '/model.bin',
        ]);
    }

    /**
     * Indicate that the artifact is a Docker image.
     */
    public function dockerImage(): static
    {
        return $this->state(fn(array $attributes) => [
            'artifact_type' => ArtifactType::DOCKER_IMAGE,
            'uri' => 'docker://registry.example.com/ai-models/' . fake()->uuid() . ':latest',
        ]);
    }

    /**
     * Indicate that the artifact has no checksum.
     */
    public function withoutChecksum(): static
    {
        return $this->state(fn(array $attributes) => [
            'checksum' => null,
        ]);
    }

    /**
     * Indicate that the artifact has no size.
     */
    public function withoutSize(): static
    {
        return $this->state(fn(array $attributes) => [
            'size_bytes' => null,
        ]);
    }
}
