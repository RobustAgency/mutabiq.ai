<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\Control\Status;
use App\Enums\Control\TestingMethod;
use App\Enums\Control\TestingFrequency;
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
            'reference' => $this->faker->bothify('REF-###-???'),
            'objective' => $this->faker->paragraphs(2, true),
            'testing_method' => $this->faker->randomElement(TestingMethod::cases())->value,
            'testing_frequency' => $this->faker->randomElement(TestingFrequency::cases())->value,
            'evidence_expectations' => $this->faker->paragraphs(2, true),
            'applicability_criteria' => $this->faker->paragraphs(2, true),
            'status' => $this->faker->randomElement(Status::cases())->value,
            'last_test_date' => $this->faker->date(),
            'next_test_due' => $this->faker->date(),
        ];
    }
}
