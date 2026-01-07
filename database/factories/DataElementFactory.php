<?php

namespace Database\Factories;

use App\Models\DataSource;
use App\Models\DataElement;
use App\Models\Organization;
use App\Enums\DataElement\Status;
use App\Enums\DataElement\DataType;
use App\Enums\DataElement\DataSteward;
use App\Enums\DataElement\Sensitivity;
use App\Enums\DataElement\DefaultMaskingMethod;
use App\Enums\DataElement\PersonalDataCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataElement>
 */
class DataElementFactory extends Factory
{
    protected $model = DataElement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $personalDataType = null;
        $maskingMethod = null;

        if (fake()->boolean(70)) {
            $personalDataType = fake()->randomElement(PersonalDataCategory::cases())->value;
        }

        if (fake()->boolean(50)) {
            $maskingMethod = fake()->randomElement(DefaultMaskingMethod::cases())->value;
        }

        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(2, true),
            'data_type' => fake()->randomElement(DataType::cases())->value,
            'format' => fake()->optional(0.5)->word(),
            'business_definition' => fake()->sentence(),
            'data_steward' => fake()->randomElement(DataSteward::cases())->value,
            'status' => fake()->randomElement(Status::cases())->value,
            'data_source_id' => DataSource::factory(),
            'database_name' => fake()->word(),
            'schema_name' => fake()->optional(0.5)->word(),
            'table_name' => fake()->word(),
            'column_name' => fake()->word(),
            'used_in_datasets' => json_encode([]),
            'is_nullable' => fake()->boolean(),
            'is_unique' => fake()->boolean(),
            'default_value' => fake()->optional(0.5)->word(),
            'validation_rule' => fake()->optional(0.5)->sentence(),
            'sample_values' => fake()->optional(0.5)->sentence(),
            'sensitivity' => fake()->randomElement(Sensitivity::cases())->value,
            'contains_personal_data' => fake()->boolean(),
            'personal_data_type' => $personalDataType,
            'contains_sensitive_data' => fake()->boolean(),
            'default_masking_method' => $maskingMethod,
            'cde_flag' => fake()->boolean(),
            'cde_categories' => json_encode([]),
        ];
    }
}
