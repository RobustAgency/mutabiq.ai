<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Control>
 */
class ControlFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->sentence(6, true),
            'code' => strtoupper($this->faker->unique()->bothify('CTL-###??')),
            'question' => $this->faker->optional()->sentence(10, true),
            'summary' => $this->faker->optional()->paragraph(2, true),
            'description' => $this->faker->optional()->paragraphs(3, true),
        ];
    }
}
