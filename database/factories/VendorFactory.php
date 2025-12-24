<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Enums\Vendor\Type;
use App\Enums\Vendor\RiskTier;
use App\Enums\Vendor\VendorStatus;
use App\Enums\Vendor\DataProcessingRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $primaryContacts = $this->faker->boolean(70) ? [
            [
                'name' => $this->faker->name(),
                'email' => $this->faker->companyEmail(),
                'role' => $this->faker->randomElement(['Account Manager', 'Technical Lead', 'Compliance Officer']),
                'phone' => $this->faker->phoneNumber(),
                'primary' => true,
            ],
        ] : null;

        $metadata = $this->faker->boolean(60) ? [
            'website' => $this->faker->url(),
            'sub_processors_url' => $this->faker->boolean(50) ? $this->faker->url() : null,
            'parent_company' => $this->faker->boolean(50) ? $this->faker->company() : null,
            'residency_options' => $this->faker->randomElements(['US', 'EU', 'UK', 'AE'], $this->faker->numberBetween(1, 3)),
        ] : null;

        return [
            'organization_id' => \App\Models\Organization::factory(),
            'vendor_name' => $this->faker->company(),
            'legal_name' => $this->faker->company().' '.$this->faker->randomElement(['Inc.', 'Ltd.', 'LLC', 'Corp.']),
            'hq_country' => $this->faker->randomElement(['US', 'GB', 'DE', 'FR', 'AE', 'SG', 'CA']),
            'risk_tier' => $this->faker->randomElement(RiskTier::cases())->value,
            'status' => $this->faker->randomElement(VendorStatus::cases())->value,
            'type' => array_map(fn ($type) => $type->value, $this->faker->randomElements(Type::cases(), $this->faker->numberBetween(1, 3), false)),
            'data_processing_role' => $this->faker->randomElement(DataProcessingRole::cases())->value,
            'service_provided' => $this->faker->boolean(80) ? $this->faker->sentence(6) : null,
            'primary_contacts' => $primaryContacts,
            'metadata' => $metadata,
            'duns_number' => $this->faker->boolean(50) ? $this->faker->unique()->numerify('#########') : null,
            'lei_number' => $this->faker->boolean(50) ? $this->faker->unique()->bothify('??????????????????') : null,
            'tax_id' => $this->faker->boolean(50) ? $this->faker->unique()->bothify('??########') : null,
            'stock_ticker' => $this->faker->boolean(50) ? strtoupper($this->faker->bothify('???')) : null,
            'notes' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
        ];
    }
}
