<?php

namespace Database\Factories;

use App\Enums\GovernancePilar;
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
            'description' => $this->faker->paragraph,
            'governance_pilar' => $this->faker->randomElement(GovernancePilar::cases()),
            'progress' => $this->faker->numberBetween(0, 100),
        ];
    }
}
