<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uniquePart = substr(Str::uuid(), 0, 8);
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->company(),
            'website' => $uniquePart . $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
        ];
    }
}
