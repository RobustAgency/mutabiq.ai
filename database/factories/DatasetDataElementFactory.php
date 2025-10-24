<?php

namespace Database\Factories;

use App\Enums\DataElement\CdeCategory;
use App\Enums\DatasetElementMap\CdeInDataset;
use App\Enums\DatasetElementMap\Deprecated;
use App\Enums\DatasetElementMap\Nullable;
use App\Enums\DatasetElementMap\PiiOverride;
use App\Enums\DatasetElementMap\SensitivityOverride;
use App\Models\DataElement;
use App\Models\Dataset;
use App\Models\DatasetDataElement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\DatasetElement>
 */
class DatasetDataElementFactory extends Factory
{
    protected $model = DatasetDataElement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dataset_id' => Dataset::factory(),
            'data_element_id' => DataElement::factory(),
            'column_name' => fake()->word(),
            'nullable' => fake()->randomElement(Nullable::cases()),
            'sensitivity_override' => fake()->optional()->randomElement(SensitivityOverride::cases()),
            'pii_override' => fake()->randomElement(PiiOverride::cases()),
            'transform_applied' => fake()->optional()->word(),
            'quality_rules_applied' => fake()->optional()->sentence(),
            'cde_in_dataset' => fake()->randomElement(CdeInDataset::cases()),
            'cde_category_in_dataset' => fake()->optional()->randomElement(CdeCategory::cases()),
            'lineage_source_column' => fake()->optional()->word(),
            'deprecated' => fake()->randomElement(Deprecated::cases()),
        ];
    }
}
