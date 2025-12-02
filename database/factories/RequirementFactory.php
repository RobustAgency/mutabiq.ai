<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Requirement>
 */
class RequirementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => $this->faker->unique()->bothify('REQ-####'),
            'requirement_text' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['security', 'human_rights', 'privacy']),
            'applicability' => $this->faker->sentence(),
            'effective_from' => $this->faker->date(),
            'effective_to' => $this->faker->optional()->date(),
            'supersedes_req_id' => null,
            'superseded_by_req_id' => null,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'tags' => $this->faker->randomElements(['security', 'compliance', 'performance', 'usability'], 2),
        ];
    }
}
