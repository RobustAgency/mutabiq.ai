<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Enums\DataSource\Status;
use App\Enums\DataSource\OwnerTeam;
use App\Enums\DataSource\DataDomain;
use App\Enums\DataSource\SystemType;
use App\Enums\DataSource\HostingModel;
use App\Enums\DataSource\DataResidency;
use App\Enums\DataSource\CriticalityLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataSource>
 */
class DataSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company().' '.fake()->randomElement(['Database', 'Data Lake', 'API', 'Storage']),
            'description' => fake()->sentence(),
            'system_type' => fake()->randomElement(SystemType::cases())->value,
            'owner_team' => fake()->randomElement(OwnerTeam::cases())->value,
            'data_domains' => fake()->randomElements(
                array_map(fn ($case) => $case->value, DataDomain::cases()),
                fake()->numberBetween(1, 2)
            ),
            'residency' => fake()->randomElement(DataResidency::cases())->value,
            'criticality_level' => fake()->boolean() ? fake()->randomElement(CriticalityLevel::cases())->value : null,
            'hosting_model' => fake()->randomElement(HostingModel::cases())->value,
            'technical_owner' => fake()->randomElement(OwnerTeam::cases())->value,
            'business_owner' => fake()->randomElement(OwnerTeam::cases())->value,
            'last_review_date' => fake()->optional()->date(),
            'next_review_date' => fake()->optional()->date(),
            'status' => fake()->randomElement(Status::cases())->value,
        ];
    }
}
