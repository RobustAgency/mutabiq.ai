<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\RiskMethodology;
use App\Enums\AiRiskRegister\RiskLevel;
use App\Enums\AiRiskRegister\RiskStatus;
use App\Enums\AiRiskRegister\RiskCategory;
use App\Enums\AiRiskRegister\RiskDecision;
use App\Enums\AiRiskRegister\ReviewCadence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiRiskRegister>
 */
class AiRiskRegisterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'risk_methodology_id' => RiskMethodology::factory(),
            'title' => $this->faker->sentence(),
            'risk_category' => $this->faker->randomElement(array_map(fn ($c) => $c->value, RiskCategory::cases())),
            'ai_model_id' => AiModel::factory(),
            'ai_model_version_id' => null,
            'use_case_id' => null,
            'description' => $this->faker->paragraph(),
            'related_controls' => null,
            'likelihood_code' => $this->faker->randomElement(['L1', 'L2', 'L3', 'L4', 'L5']),
            'impact_code' => $this->faker->randomElement(['I1', 'I2', 'I3', 'I4', 'I5']),
            'inherent_score' => $this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']),
            'residual_score' => $this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']),
            'risk_level' => $this->faker->randomElement(array_map(fn ($c) => $c->value, RiskLevel::cases())),
            'decision' => $this->faker->randomElement(array_map(fn ($c) => $c->value, RiskDecision::cases())),
            'risk_owner' => Stakeholder::factory(),
            'review_cadence' => $this->faker->randomElement(array_map(fn ($c) => $c->value, ReviewCadence::cases())),
            'next_review_due' => $this->faker->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(array_map(fn ($c) => $c->value, RiskStatus::cases())),
            'linked_assessment_id' => null,
            'linked_incident_id' => null,
            'linked_capa_id' => null,
            'evidence_link' => $this->faker->optional()->url(),
            'likelihood_label_snapshot' => $this->faker->randomElement(['Rare', 'Unlikely', 'Possible', 'Likely', 'Almost Certain']),
            'impact_label_snapshot' => $this->faker->randomElement(['Negligible', 'Minor', 'Moderate', 'Major', 'Catastrophic']),
            'method_name_snapshot' => $this->faker->randomElement(['5x5 Matrix', '3x3 Matrix', 'ISO 31000', 'NIST RMF']),
            'created_by' => $this->faker->safeEmail(),
        ];
    }
}
