<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AiModel;
use App\Models\UseCase;
use App\Models\AiModelVersion;
use App\Models\User;
use App\Models\Organization;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiModelUseCase>
 */
class AiModelUseCaseFactory extends Factory
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
            'use_case_id' => UseCase::factory(),
            'ai_model_version_id' => AiModelVersion::factory(),
            'relationship_type' => $this->faker->randomElement(['primary', 'secondary', 'experimental', 'backup']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
