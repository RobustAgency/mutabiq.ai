<?php

namespace Database\Factories;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\ConsentStatus;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\LegalBasis;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\UserConsent;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserConsent>
 */
class UserConsentFactory extends Factory
{
    protected $model = UserConsent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $effectiveFrom = fake()->dateTimeBetween('-1 year', 'now');

        // Generate multiple consent purposes (1-4 purposes)
        $selectedPurposes = fake()->randomElements(ConsentPurpose::cases(), fake()->numberBetween(1, 4));
        $purposeValues = array_map(fn($purpose) => $purpose->value, $selectedPurposes);

        return [
            'organization_id' => Organization::factory(),
            'subject_key' => fake()->regexify('[A-Z0-9]{32}'),
            'subject_realm' => fake()->randomElement(SubjectRealm::cases()),
            'jurisdiction' => fake()->randomElement(Jurisdiction::cases()),
            'consent_purpose' => $purposeValues,
            'consent_status' => fake()->randomElement(ConsentStatus::cases()),
            'legal_basis' => fake()->randomElement(LegalBasis::cases()),
            'source_system' => fake()->randomElement(['CRM', 'Portal', 'Mobile App', 'Website', 'API']),
            'evidence_ref' => fake()->regexify('EVD-[0-9]{8}'),
            'effective_from' => $effectiveFrom,
            'effective_to' => fake()->optional(0.3)->dateTimeBetween($effectiveFrom, '+2 years'),
            'scope' => fake()->optional()->sentence(20),
        ];
    }
}
