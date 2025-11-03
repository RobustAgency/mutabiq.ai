<?php

namespace Database\Factories;

use App\Enums\DataElement\CdeCategory;
use App\Enums\DataElement\CdeFlag;
use App\Enums\DataElement\DataType;
use App\Enums\DataElement\PersonalDataCategory;
use App\Enums\DataElement\PiiFlag;
use App\Enums\DataElement\Sensitivity;
use App\Enums\DataElement\SpecialCategoryFlag;
use App\Models\DataElement;
use App\Models\Organization;
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
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'business_definition' => fake()->sentence(),
            'data_type' => fake()->randomElement(DataType::cases()),
            'format' => fake()->optional()->word(),
            'sensitivity' => fake()->randomElement(Sensitivity::cases()),
            'pii_flag' => fake()->randomElement(PiiFlag::cases()),
            'personal_data_category' => fake()->optional()->randomElement(PersonalDataCategory::cases()),
            'special_category_flag' => fake()->randomElement(SpecialCategoryFlag::cases()),
            'cde_flag' => fake()->randomElement(CdeFlag::cases()),
            'cde_category' => fake()->optional()->randomElement(CdeCategory::cases()),
            'owner_team' => fake()->optional()->word(),
            'quality_rules_ref' => fake()->optional()->sentence(),
            'catalog_column_id' => fake()->optional()->regexify('COL[0-9]{6}'),
        ];
    }
}
