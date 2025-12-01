<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\RiskMethodology;
use Illuminate\Database\Eloquent\Factories\Factory;

class RiskMethodologyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RiskMethodology::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->words(3, true),
            'likelihood_scale' => ['rare', 'possible', 'likely'],
            'impact_scale' => ['minor', 'moderate', 'major'],
            'matrix_rule' => ['low', 'medium', 'high'],
            'acceptance_thresholds' => 'hola',
            'aggregation_logic' => 'mean',
            'review_policy' => $this->faker->sentence(),
            'effective_from' => $this->faker->date(),
            'effective_to' => null,
            'owner_team' => $this->faker->company(),
            'source_created_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
        ];
    }
}
