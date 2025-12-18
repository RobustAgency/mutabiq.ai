<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\Framework;
use App\Models\Organization;
use App\Enums\GovernancePillar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
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
            'name' => $this->faker->sentence(3),
            'framework_id' => Framework::factory(),
            'description' => $this->faker->paragraph,
            'governance_pillar' => $this->faker->randomElement(GovernancePillar::cases()),
            'progress' => $this->faker->numberBetween(0, 100),
        ];
    }
}
