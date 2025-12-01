<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\AiModel;
use App\Enums\VersionType;
use App\Models\Organization;
use App\Enums\LifecycleStage;
use App\Enums\ComplexityLevel;
use App\Enums\DeploymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiModelVersion>
 */
class AiModelVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'ai_model_id' => AiModel::factory(),
            'version_number' => 'v'.$this->faker->numerify('#.##.#'),
            'version_type' => $this->faker->randomElement(array_map(fn ($c) => $c->value, VersionType::cases())),
            'release_role' => $this->faker->optional()->randomElement(array_map(fn ($c) => $c->value, \App\Enums\VersionReleaseRole::cases())),
            'source_type' => $this->faker->optional()->randomElement(array_map(fn ($c) => $c->value, \App\Enums\VersionSourceType::cases())),
            'org_involvement' => $this->faker->optional()->randomElement(array_map(fn ($c) => $c->value, \App\Enums\VersionOrgInvolvement::cases())),
            'description' => $this->faker->paragraph,
            'release_notes' => $this->faker->optional()->text,
            'release_date' => $this->faker->date(),
            'architecture_type' => $this->faker->randomElement(array_map(fn ($c) => $c->value, \App\Enums\VersionArchitectureType::cases())),
            'model_file_size_gb' => $this->faker->randomFloat(2, 0.1, 50),
            'training_duration_hours' => $this->faker->optional()->numberBetween(1, 100),
            'complexity_level' => $this->faker->randomElement(array_map(fn ($c) => $c->value, ComplexityLevel::cases())),
            'parameter_count' => $this->faker->optional()->numberBetween(1e6, 1e12),
            'input_modalities' => $this->faker->randomElements(['text', 'image', 'audio', 'video', 'structured_data', 'time_series'], $this->faker->numberBetween(1, 3)),
            'output_modalities' => $this->faker->randomElements(['text', 'image', 'audio', 'classification', 'regression', 'embedding', 'structured_data'], $this->faker->numberBetween(1, 3)),
            'deployment_status' => $this->faker->randomElement(array_map(fn ($c) => $c->value, DeploymentStatus::cases())),
            'lifecycle_stage' => $this->faker->randomElement(array_map(fn ($c) => $c->value, LifecycleStage::cases())),
            'deployment_environments' => $this->faker->randomElements(['cloud', 'on-premise', 'edge', 'hybrid'], $this->faker->numberBetween(1, 3)),
            'approval_status' => $this->faker->optional()->randomElement(array_map(fn ($c) => $c->value, \App\Enums\VersionApprovalStatus::cases())),
            'customizations_applied' => $this->faker->optional()->randomElements([
                'fine_tuning',
                'prompt_engineering',
                'model_compression',
                'quantization',
                'distillation',
                'pruning',
            ], $this->faker->numberBetween(0, 3)),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
