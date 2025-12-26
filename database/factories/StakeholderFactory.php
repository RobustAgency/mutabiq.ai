<?php

namespace Database\Factories;

use App\Models\Stakeholder;
use App\Models\Organization;
use App\Enums\Stakeholder\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stakeholder>
 */
class StakeholderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Stakeholder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'type' => fake()->randomElement(Type::cases())->value,
            'display_name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'org_unit' => fake()->optional()->randomElement(['Engineering', 'Sales', 'Marketing', 'Operations']),
            'email' => fake()->safeEmail(),
            'secondary_email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'mobile' => fake()->optional()->phoneNumber(),
            'role_tags' => fake()->randomElements(['admin', 'user', 'manager'], rand(1, 2)),
            'timezone' => fake()->timezone(),
            'classification' => fake()->randomElement(['internal', 'external']),
            'country' => fake()->countryCode(),
            'external_ref' => fake()->optional()->uuid(),
            'employee_id' => fake()->optional()->numerify('EMP#####'),
            'cost_center' => fake()->optional()->numerify('CC#####'),
            'manager' => fake()->optional()->name(),
            'delegate' => fake()->optional()->name(),
            'status' => 'active',
            'notes' => fake()->optional()->paragraph(),
            'start_date' => fake()->optional()->date(),
            'end_date' => fake()->optional()->date(),
        ];
    }
}
