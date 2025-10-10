<?php

namespace Database\Factories;

use App\Enums\GovernancePillar;
use App\Models\Framework;
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
            'name' => $this->faker->sentence(3),
            'framework_id' => Framework::factory(),
            'description' => $this->faker->paragraph,
            'governance_pillar' => $this->faker->randomElement(GovernancePillar::cases()),
            'progress' => $this->faker->numberBetween(0, 100),
        ];
    }
}
