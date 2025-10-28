<?php

namespace Database\Factories;

use App\Models\AiAsset;
use App\Models\Agreement;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiAsset>
 */
class AiAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasVendor = $this->faker->boolean(80);

        $vendorEffectiveFrom = null;
        $vendorEffectiveTo = null;
        $vendorAgreementId = null;
        $vendorAssessmentId = null;

        if ($hasVendor) {
            $vendorEffectiveFrom = $this->faker->dateTimeBetween('-1 year', 'now');
            $vendorEffectiveTo = $this->faker->dateTimeBetween('now', '+2 years');

            if ($this->faker->boolean(70)) {
                $agreement = Agreement::factory()->create();
                $vendorAgreementId = $agreement->id;
            }

            if ($this->faker->boolean(60)) {
                $vendorAssessmentId = $this->faker->numberBetween(1, 1000);
            }
        }

        return [
            'vendor_id' => $hasVendor ? Vendor::factory() : null,
            'vendor_effective_from' => $vendorEffectiveFrom,
            'vendor_effective_to' => $vendorEffectiveTo,
            'vendor_agreement_id' => $vendorAgreementId,
            'vendor_assessment_id' => $vendorAssessmentId,
        ];
    }

    /**
     * Indicate that the AI asset has a vendor.
     */
    public function withVendor(): static
    {
        return $this->state(fn(array $attributes) => [
            'vendor_id' => Vendor::factory(),
            'vendor_effective_from' => now()->subMonth(),
            'vendor_effective_to' => now()->addYear(),
        ]);
    }

    /**
     * Indicate that the AI asset has no vendor.
     */
    public function withoutVendor(): static
    {
        return $this->state(fn(array $attributes) => [
            'vendor_id' => null,
            'vendor_effective_from' => null,
            'vendor_effective_to' => null,
            'vendor_agreement_id' => null,
            'vendor_assessment_id' => null,
        ]);
    }
}
