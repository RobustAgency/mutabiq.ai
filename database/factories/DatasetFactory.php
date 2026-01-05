<?php

namespace Database\Factories;

use App\Models\DataSource;
use App\Models\Organization;
use App\Enums\Dataset\Status;
use App\Enums\Dataset\Purpose;
use App\Enums\Dataset\SizeUnit;
use App\Enums\Dataset\DataSteward;
use App\Enums\Dataset\LicenseType;
use App\Enums\Dataset\Sensitivity;
use App\Enums\Dataset\PrimaryLanguage;
use App\Enums\Dataset\ContainPersonalData;
use App\Enums\Dataset\CrossBorderTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dataset>
 */
class DatasetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dataSources = DataSource::pluck('id')->toArray();
        $sourceCount = min(fake()->numberBetween(1, 3), count($dataSources) ?: 1);
        $sourceIds = count($dataSources) > 0
            ? fake()->randomElements($dataSources, $sourceCount)
            : [1, 2, 3];

        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true).' Dataset',
            'description' => fake()->optional()->sentence(),
            'source_ids' => $sourceIds,
            'purpose' => fake()->randomElement(Purpose::cases())->value,
            'owner_team' => fake()->randomElement(['Data Science', 'Engineering', 'Analytics', 'Research', 'Operations']),
            'data_steward' => fake()->randomElement(DataSteward::cases())->value,
            'status' => fake()->randomElement(Status::cases())->value,
            'estimated_row_count' => fake()->optional()->numberBetween(1000, 10000000),
            'estimated_size' => fake()->optional()->numberBetween(100, 10000),
            'size_unit' => fake()->boolean() ? fake()->randomElement(SizeUnit::cases())->value : null,
            'retention_period' => fake()->optional()->randomElement(['30 days', '90 days', '1 year', '7 years', 'indefinite']),
            'primary_languages' => fake()->boolean() ? fake()->randomElements(
                array_map(fn ($c) => $c->value, PrimaryLanguage::cases()),
                fake()->numberBetween(1, 3)
            ) : null,
            'contains_personal_data' => fake()->randomElement(ContainPersonalData::cases())->value,
            'sensitivity' => fake()->randomElement(Sensitivity::cases())->value,
            'cross_border_transfer' => fake()->randomElement(CrossBorderTransfer::cases())->value,
            'license_type' => fake()->boolean() ? fake()->randomElement(LicenseType::cases())->value : null,
        ];
    }
}
