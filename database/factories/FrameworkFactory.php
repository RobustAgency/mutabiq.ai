<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Framework>
 */
class FrameworkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->word(),
            'code' => strtoupper($this->faker->lexify('???')),
            'type' => $this->faker->randomElement(['Type A', 'Type B', 'Type C']),
            'geography' => $this->faker->country(),
            'category' => $this->faker->randomElement(['mandatory', 'voluntary']),
            'version' => $this->faker->numerify('v#.##'),
            'release_date' => $this->faker->date(),
            'is_published' => $this->faker->boolean(),
            'description' => $this->faker->paragraph(),
            'authority_publisher' => $this->faker->company(),
            'binding_level' => $this->faker->randomElement(['High', 'Medium', 'Low']),
            'sector_applicability' => $this->faker->randomElement(['Finance', 'Healthcare', 'Education']),
            'risk_class_coverage' => $this->faker->randomElement(['Low', 'Medium', 'High']),
            'certification_attestation' => $this->faker->sentence(),
            'assessment_mode' => $this->faker->randomElement(['Self-assessment', 'Third-party assessment']),
        ];
    }
}
