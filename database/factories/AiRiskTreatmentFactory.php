<?php

namespace Database\Factories;

use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\AiRiskRegister;
use App\Models\AiRiskTreatment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiRiskTreatmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AiRiskTreatment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'ai_risk_register_id' => AiRiskRegister::factory(),
            'treatment_type' => $this->faker->randomElement(['mitigation', 'remediation', 'transfer', 'acceptance']),
            'plan_summary' => $this->faker->sentence(12),
            'owner_stakeholder_id' => Stakeholder::factory(),
            'assignee' => [1, 2, 3, 4, 5],
            'due_date' => $this->faker->dateTimeBetween('now', '+90 days')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'completed', 'cancelled']),
            'expected_residual_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'result_verification' => $this->faker->sentence(6),
            'evidence_link' => $this->faker->optional()->url(),
            'linked_capa_id' => $this->faker->optional()->numberBetween(1, 1000),
            'closed_at' => $this->faker->optional(0.3)->dateTimeBetween('-30 days', '+30 days'),
        ];
    }
}
