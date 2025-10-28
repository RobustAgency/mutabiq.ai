<?php

namespace Database\Factories;

use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\DatasetSubjectPopulation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DatasetSubjectPopulation>
 */
class DatasetSubjectPopulationFactory extends Factory
{
    protected $model = DatasetSubjectPopulation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dataset_id' => Dataset::factory(),
            'snapshot_id' => $this->faker->boolean(70) ? DatasetSnapshot::factory() : null,
            'subject_realm' => $this->faker->randomElement(SubjectRealm::cases())->value,
            'jurisdiction' => $this->faker->randomElement(Jurisdiction::cases())->value,
            'subjects_total' => $this->faker->numberBetween(1000, 1000000),
            'as_of' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
