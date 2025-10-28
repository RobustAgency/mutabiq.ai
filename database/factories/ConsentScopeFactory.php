<?php

namespace Database\Factories;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\Dataset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsentScope>
 */
class ConsentScopeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $effectiveFrom = fake()->dateTimeBetween('-1 year', 'now');
        $hasEndDate = fake()->boolean(60);

        return [
            'dataset_id' => Dataset::factory(),
            'purpose' => fake()->randomElement(ConsentPurpose::cases()),
            'subject_realm' => fake()->randomElement(SubjectRealm::cases()),
            'jurisdiction' => fake()->randomElement(Jurisdiction::cases()),
            'effective_from' => $effectiveFrom,
            'effective_to' => $hasEndDate ? fake()->dateTimeBetween($effectiveFrom, '+2 years') : null,
        ];
    }
}
