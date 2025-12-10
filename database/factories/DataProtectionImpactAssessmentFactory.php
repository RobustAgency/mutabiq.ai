<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\AiModel;
use App\Models\RecordOfProcessingActivity;
use App\Models\DataProtectionImpactAssessment;
use App\Enums\DataProtectionImpactAssessment\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\DataProtectionImpactAssessment\Status;
use App\Enums\DataProtectionImpactAssessment\RiskLevel;
use App\Enums\DataProtectionImpactAssessment\Jurisdiction;
use App\Enums\DataProtectionImpactAssessment\FinalDecision;
use App\Enums\DataProtectionImpactAssessment\LinkedAssetsType;
use App\Enums\DataProtectionImpactAssessment\ResidualRiskLevel;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataProtectionImpactAssessment>
 */
class DataProtectionImpactAssessmentFactory extends Factory
{
    protected $model = DataProtectionImpactAssessment::class;

    public function definition(): array
    {
        $riskLevel = $this->faker->randomElement(RiskLevel::cases());
        $stage = $this->faker->randomElement(Stage::cases());
        $status = $this->faker->randomElement(Status::cases());
        $finalDecision = $this->faker->randomElement(FinalDecision::cases());
        $residualRiskLevel = $this->faker->randomElement(ResidualRiskLevel::cases());

        return [
            'dpia_code' => 'DPIA-'.$this->faker->year.'-'.$this->faker->unique()->uuid(),
            'dpia_name' => $this->faker->sentence(3),
            'ropa_id' => RecordOfProcessingActivity::factory(),
            'linked_ai_model_id' => $this->faker->optional(0.3)->randomElement(AiModel::query()->pluck('id')->toArray()) ?? null,
            'linked_asset_type' => $this->faker->randomElement(array_map(fn ($case) => $case->value, LinkedAssetsType::cases())),
            'automated_trigger' => $this->faker->boolean(),
            'trigger_reason' => $this->faker->sentence(),
            'risk_level' => $riskLevel->value,
            'risk_score' => $this->faker->numberBetween(1, 25),
            'stage' => $stage->value,
            'completion_percentage' => $this->faker->numberBetween(0, 100),
            'necessity_justification' => $stage === Stage::NECESSITY ? $this->faker->paragraph() : null,
            'proportionality_assessment' => $this->faker->paragraph(),
            'alternatives_considered' => $this->faker->paragraph(),
            'identified_risks' => $stage === Stage::RISK_IDENTIFICATION ? $this->faker->paragraph() : null,
            'likelihood_assessment' => $this->faker->paragraph(),
            'impact_assessment' => $this->faker->paragraph(),
            'mitigation_measures' => $stage === Stage::MITIGATION ? $this->faker->paragraph() : null,
            'residual_risk_level' => in_array($stage, [Stage::DPO_CONSULTATION, Stage::APPROVAL, Stage::COMPLETED]) ? $residualRiskLevel->value : null,
            'dpo_consulted' => $this->faker->boolean(50),
            'dpo_consultation_date' => $this->faker->optional(0.5)->dateTime(),
            'dpo_advice' => $this->faker->optional(0.4)->paragraph(),
            'dpo_user_id' => $this->faker->optional(0.3)->randomElement(User::query()->pluck('id')->toArray()) ?? null,
            'stakeholders_consulted' => $this->faker->randomElements(['stakeholder_1', 'stakeholder_2', 'stakeholder_3'], random_int(0, 2)),
            'stakeholder_feedback' => $this->faker->optional(0.5)->paragraph(),
            'data_subjects_consulted' => $this->faker->boolean(40),
            'consultation_method' => $this->faker->randomElement(['email', 'meeting', 'survey', 'form']),
            'final_decision' => $stage === Stage::APPROVAL ? $finalDecision->value : null,
            'approval_date' => $stage === Stage::APPROVAL ? $this->faker->dateTime() : null,
            'approved_by' => $stage === Stage::APPROVAL ? $this->faker->randomElement(User::query()->pluck('id')->toArray()) ?? User::factory() : null,
            'conditions' => $finalDecision === FinalDecision::APPROVED_WITH_CONDITIONS ? $this->faker->paragraph() : null,
            'status' => $status->value,
            'review_frequency_months' => $this->faker->randomElement([6, 12, 24, 36]),
            'next_review_date' => $this->faker->optional(0.7)->dateTime(),
            'applicable_jurisdictions' => $this->faker->randomElements(
                array_map(fn ($case) => $case->value, Jurisdiction::cases()),
                $this->faker->numberBetween(1, count(Jurisdiction::cases()))
            ),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'version' => 1,
        ];
    }

    /**
     * State for a DPIA in draft status
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::DRAFT->value,
            'stage' => Stage::SCREENING->value,
            'completion_percentage' => 0,
        ]);
    }

    /**
     * State for a DPIA in progress
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::IN_PROGRESS->value,
            'stage' => $this->faker->randomElement([Stage::NECESSITY, Stage::RISK_IDENTIFICATION, Stage::MITIGATION]),
            'completion_percentage' => $this->faker->numberBetween(20, 80),
        ]);
    }

    /**
     * State for a completed DPIA
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::COMPLETED->value,
            'stage' => Stage::COMPLETED->value,
            'completion_percentage' => 100,
            'final_decision' => FinalDecision::APPROVED->value,
            'approval_date' => $this->faker->dateTime(),
            'approved_by' => User::factory(),
        ]);
    }

    /**
     * State for a high-risk DPIA
     */
    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => RiskLevel::HIGH->value,
            'risk_score' => $this->faker->numberBetween(15, 25),
            'residual_risk_level' => ResidualRiskLevel::HIGH->value,
        ]);
    }

    /**
     * State for a low-risk DPIA
     */
    public function lowRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => RiskLevel::LOW->value,
            'risk_score' => $this->faker->numberBetween(1, 5),
            'residual_risk_level' => ResidualRiskLevel::LOW->value,
        ]);
    }
}
