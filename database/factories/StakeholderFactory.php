<?php

namespace Database\Factories;

use App\Enums\Stakeholder\Type;
use App\Models\Stakeholder;
use App\Models\Organization;
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
            'legal_name' => fake()->optional()->company(),
            'org_unit' => fake()->optional()->randomElement(['Engineering', 'Sales', 'Marketing', 'Operations']),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'vendor_id' => fake()->optional()->numerify('VND-####'),
            'role_tags' => fake()->randomElements(['admin', 'user', 'manager'], rand(1, 2)),
            'timezone' => fake()->timezone(),
            'classification' => fake()->randomElement(['internal', 'external']),
            'country' => fake()->countryCode(),
            'external_ref' => fake()->optional()->uuid(),
            'active' => true,
        ];
    }
}
