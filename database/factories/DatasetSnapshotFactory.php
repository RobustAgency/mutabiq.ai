<?php

namespace Database\Factories;

use App\Enums\DatasetSnapshot\ResidencyZone;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DatasetSnapshot>
 */
class DatasetSnapshotFactory extends Factory
{
    protected $model = DatasetSnapshot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'dataset_id' => Dataset::factory(),
            'version_tag' => fake()->randomElement(['v1.0', 'v1.1', 'v2.0', 'v2.1', 'v3.0']),
            'time_range_start' => fake()->optional()->dateTimeBetween('-1 year', '-1 month'),
            'time_range_end' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'row_count' => fake()->optional()->numberBetween(1000, 10000000),
            'quality_checksums' => fake()->optional()->sha256(),
            'pii_element_count' => fake()->optional()->numberBetween(0, 50),
            'special_category_element_count' => fake()->optional()->numberBetween(0, 20),
            'masking_anonymization_method' => fake()->optional()->randomElement([
                'Full Masking',
                'Tokenization',
                'Pseudonymization',
                'Encryption',
            ]),
            'privacy_transform_evidence_ref' => fake()->optional()->regexify('PTE-[0-9]{6}'),
            'residency_zone' => fake()->randomElement(ResidencyZone::cases()),
            'storage_uri' => fake()->url() . '/snapshots/' . fake()->regexify('[a-z0-9]{16}'),
        ];
    }
}
