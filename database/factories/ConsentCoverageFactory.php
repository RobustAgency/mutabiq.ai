<?php

namespace Database\Factories;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsentCoverage>
 */
class ConsentCoverageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjectsTotal = fake()->numberBetween(1000, 100000);
        $subjectsWithConsent = fake()->numberBetween(0, $subjectsTotal);
        $coveragePct = $subjectsTotal > 0
            ? round(($subjectsWithConsent / $subjectsTotal) * 100, 2)
            : 0.00;

        $asOf = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'dataset_id' => Dataset::factory(),
            'snapshot_id' => fake()->boolean(70) ? DatasetSnapshot::factory() : null,
            'purpose' => fake()->randomElement(ConsentPurpose::cases()),
            'jurisdiction' => fake()->randomElement(Jurisdiction::cases()),
            'as_of' => $asOf,
            'subjects_total' => $subjectsTotal,
            'subjects_with_valid_consent' => $subjectsWithConsent,
            'coverage_pct' => $coveragePct,
            'evidence_ref' => 'EVD-' . strtoupper(Str::random(10)),
            'created_at' => $asOf,
        ];
    }
}
