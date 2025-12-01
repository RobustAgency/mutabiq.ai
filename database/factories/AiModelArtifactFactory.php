<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\ArtifactType;
use App\Models\Organization;
use App\Models\AiModelVersion;
use App\Models\AiModelArtifact;
use App\Enums\ArtifactFileFormat;
use App\Enums\ArtifactEnvironment;
use App\Enums\ArtifactChecksumAlgorithm;
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
            'artifact_type' => fake()->randomElement(ArtifactType::cases()),
            'uri' => fake()->optional(0.5)->url(),
            'checksum_algorithm' => fake()->randomElement(ArtifactChecksumAlgorithm::cases()),
            'checksum_value' => fake()->md5(),
            'environment' => fake()->randomElement(ArtifactEnvironment::cases()),
            'file_format' => fake()->randomElement(ArtifactFileFormat::cases()),
            'size_bytes' => fake()->numberBetween(10485760, 26214400), // 10MB to 25MB
            'created_by' => User::factory(),
            'notes' => fake()->optional(0.7)->sentence(12),
        ];
    }
}
