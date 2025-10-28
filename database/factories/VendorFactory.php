<?php

namespace Database\Factories;

use App\Enums\Vendor\RiskTier;
use App\Enums\Vendor\VendorStatus;
use App\Models\Stakeholder;
use App\Models\Vendor;
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
            'residency_options' => $this->faker->randomElements(['US', 'EU', 'UK', 'AE'], $this->faker->numberBetween(1, 3)),
        ] : null;

        return [
            'vendor_name' => $this->faker->company(),
            'legal_name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Inc.', 'Ltd.', 'LLC', 'Corp.']),
            'hq_country' => $this->faker->randomElement(['US', 'GB', 'DE', 'FR', 'AE', 'SG', 'CA']),
            'risk_tier' => $this->faker->randomElement(RiskTier::cases())->value,
            'status' => $this->faker->randomElement(VendorStatus::cases())->value,
            'stakeholder_id' => Stakeholder::factory(),
            'primary_contacts' => $primaryContacts,
            'metadata' => $metadata,
            'notes' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
        ];
    }
}
