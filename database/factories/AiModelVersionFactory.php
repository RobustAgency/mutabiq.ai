<?php

namespace Database\Factories;

use App\Enums\ComplexityLevel;
use App\Enums\ComplianceStatus;
use App\Enums\DeploymentStatus;
use App\Enums\LifecycleStage;
use App\Enums\ValidationStatus;
use App\Enums\VersionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AiModel;

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
            'ai_model_id' => AiModel::factory(),
            'version_number' => $this->faker->unique()->numerify('v#.##'),
            'version_type' => VersionType::MAJOR,
            'description' => $this->faker->paragraph,
            'release_notes' => $this->faker->text,
            'release_date' => $this->faker->date(),
            'architecture_type' => $this->faker->randomElement(['transformer', 'cnn', 'rnn', 'gan']),
            'model_file_size_gb' => $this->faker->randomFloat(2, 0.1, 50),
            'training_duration_hours' => $this->faker->numberBetween(1, 100),
            'complexity_level' => ComplexityLevel::MODERATE,
            'parameter_count' => $this->faker->numberBetween(1e6, 1e12),
            'input_modalities' => $this->faker->randomElements(['text', 'image', 'audio', 'video'], $this->faker->numberBetween(1, 4)),
            'output_modalities' => $this->faker->randomElements(['text', 'image', 'audio', 'video'], $this->faker->numberBetween(1, 4)),
            'deployment_status' => DeploymentStatus::NOT_DEPLOYED,
            'lifecycle_stage' => LifecycleStage::DEVELOPMENT,
            'compliance_check_status' => ComplianceStatus::NOT_CHECKED,
            'validation_status' => ValidationStatus::IN_PROGRESS,
            'deployment_environments' => $this->faker->randomElements(['cloud', 'on-premise', 'edge'], $this->faker->numberBetween(1, 3)),
            'rollback_available' => $this->faker->boolean,
            'has_performance_data' => $this->faker->boolean,
            'performance_baseline_established' => $this->faker->boolean,
        ];
    }
}
