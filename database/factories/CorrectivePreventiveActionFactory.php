<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\Organization;
use App\Enums\CorrectivePreventiveAction\Status;
use App\Enums\CorrectivePreventiveAction\CapaType;
use App\Enums\CorrectivePreventiveAction\Priority;
use App\Enums\CorrectivePreventiveAction\OwnerTeam;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\CorrectivePreventiveAction\SourceType;
use App\Enums\CorrectivePreventiveAction\VerificationResult;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CorrectivePreventiveAction>
 */
class CorrectivePreventiveActionFactory extends Factory
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
            'source_type' => SourceType::INCIDENT->value,
            'source_reference' => $this->faker->bothify('INC-##??##'),
            'ai_model_id' => AiModel::factory(),
            'dataset_id' => null,
            'title' => $this->faker->sentence(),
            'capa_type' => $this->faker->randomElement([
                CapaType::CORRECTIVE->value,
                CapaType::PREVENTIVE->value,
                CapaType::BOTH->value,
            ]),
            'priority' => $this->faker->randomElement([
                Priority::LOW->value,
                Priority::MEDIUM->value,
                Priority::HIGH->value,
                Priority::CRITICAL->value,
            ]),
            'root_cause' => $this->faker->paragraph(),
            'actions' => $this->faker->paragraphs(2, true),
            'owner_team' => $this->faker->randomElement([
                OwnerTeam::AI_GOVERNANCE->value,
                OwnerTeam::DATA_PRIVACY_OFFICE->value,
                OwnerTeam::DATA_GOVERNANCE->value,
                OwnerTeam::ML_ENGINEERING->value,
                OwnerTeam::DATA_ENGINEERING->value,
                OwnerTeam::INFORMATION_SECURITY->value,
                OwnerTeam::LEGAL->value,
                OwnerTeam::COMPLIANCE->value,
                OwnerTeam::EXECUTIVE_LEADERSHIP->value,
                OwnerTeam::PRODUCT->value,
                OwnerTeam::CUSTOMER_SUCCESS->value,
            ]),
            'assignee' => $this->faker->name(),
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'status' => Status::NEW->value,
            'success_criteria' => null,
            'linked_training' => null,
            'estimated_cost' => null,
            'effectiveness_review_date' => null,
            'verification_result' => null,
            'evidence_link' => null,
        ];
    }

    /**
     * Indicate that the action is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::IN_PROGRESS->value,
        ]);
    }

    /**
     * Indicate that the action is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::BLOCKED->value,
        ]);
    }

    /**
     * Indicate that the action is pending verification.
     */
    public function pendingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::PENDING_VERIFICATION->value,
            'verification_result' => VerificationResult::PENDING->value,
        ]);
    }

    /**
     * Indicate that the action is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::CLOSED->value,
            'verification_result' => $this->faker->randomElement([
                VerificationResult::VERIFIED_EFFECTIVE->value,
                VerificationResult::VERIFIED_INEFFECTIVE->value,
                VerificationResult::REQUIRES_REWORK->value,
            ]),
            'evidence_link' => $this->faker->url(),
            'effectiveness_review_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the action is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Priority::HIGH->value,
        ]);
    }

    /**
     * Indicate that the action is critical priority.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Priority::CRITICAL->value,
        ]);
    }

    /**
     * Indicate that the action is corrective.
     */
    public function corrective(): static
    {
        return $this->state(fn (array $attributes) => [
            'capa_type' => CapaType::CORRECTIVE->value,
        ]);
    }

    /**
     * Indicate that the action is preventive.
     */
    public function preventive(): static
    {
        return $this->state(fn (array $attributes) => [
            'capa_type' => CapaType::PREVENTIVE->value,
        ]);
    }

    /**
     * Indicate that the action is from a risk source.
     */
    public function fromRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => SourceType::RISK_ASSESSMENT->value,
            'source_reference' => $this->faker->bothify('RISK-##??##'),
        ]);
    }

    /**
     * Indicate that the action is from an audit.
     */
    public function fromAudit(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => SourceType::AUDIT_FINDING->value,
            'source_reference' => $this->faker->bothify('AUDIT-##??##'),
        ]);
    }
}
