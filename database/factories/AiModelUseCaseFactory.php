<?php

namespace Database\Factories;

use App\Enums\AiModelUseCase\DataSensitivity;
use App\Enums\AiModelUseCase\Status;
use App\Enums\AiModelUseCase\RiskLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => Status::IN_DEVELOPMENT,
            'business_domain' => $this->faker->word,
            'business_owner_email' => $this->faker->email,
            'technical_owner_email' => $this->faker->email,
            'regulatory_scope' => $this->faker->word,
            'data_sensitivity' => DataSensitivity::CONFIDENTIAL,
            'go_live_date' => $this->faker->date,
            'expected_roi' => $this->faker->randomFloat(2, 0, 100),
            'implementation_cost' => $this->faker->randomNumber(),
            'reduction_in_time' => $this->faker->randomFloat(2, 0, 100),
            'reduction_in_cost' => $this->faker->randomNumber(),
            'increase_in_revenue' => $this->faker->randomNumber(),
            'risk_avoidance' => $this->faker->randomNumber(),
            'fte_capacity_saved' => $this->faker->randomNumber(),
            'use_case_type' => $this->faker->word,
            'value_driver' => $this->faker->word,
            'risk_level' => RiskLevel::MEDIUM,
            'overall_risk_score' => $this->faker->randomNumber(),
            'human_oversight_mode' => $this->faker->word,
            'dpia' => $this->faker->boolean,
            'aia' => $this->faker->boolean,
            'data_availability_status' => $this->faker->word,
            'data_readiness_level' => $this->faker->word,
            'data_\freshness' => $this->faker->word,
        ];
    }
}
