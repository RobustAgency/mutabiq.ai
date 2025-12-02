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
            'version' => $this->faker->numerify('v#.##'),
            'jurisdictions' => $this->faker->randomElements(['US', 'EU', 'UK', 'CA', 'AU'], 2),
            'scope' => $this->faker->sentence(6),
            'status' => $this->faker->randomElement(\App\Enums\Framework\Status::cases())->value,
            'effective_date' => $this->faker->date(),
            'source_url' => $this->faker->url(),
        ];
    }
}
