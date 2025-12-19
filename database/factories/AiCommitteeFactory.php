<?php

namespace Database\Factories;

use App\Enums\AiCommittee\Type;
use App\Enums\AiCommittee\Cadence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiCommittee>
 */
class AiCommitteeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(Type::cases())->value,
            'charter' => $this->faker->paragraph(),
            'cadence' => $this->faker->randomElement(Cadence::cases())->value,
            'owner_team' => $this->faker->randomElement(['ai-governance', 'ethics', 'compliance', 'risk-management']),
            'active' => $this->faker->boolean(80),
        ];
    }

    /**
     * Indicate that the committee is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Indicate that the committee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
