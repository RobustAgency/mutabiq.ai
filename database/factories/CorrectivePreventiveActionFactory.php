<?php

namespace Database\Factories;

use App\Enums\CorrectivePreventiveAction\CapaType;
use App\Enums\CorrectivePreventiveAction\OwnerTeam;
use App\Enums\CorrectivePreventiveAction\Priority;
use App\Enums\CorrectivePreventiveAction\SourceType;
use App\Enums\CorrectivePreventiveAction\Status;
use App\Enums\CorrectivePreventiveAction\VerificationResult;
use App\Models\AiIncident;
use App\Models\AiModel;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $incident = AiIncident::factory()->create();

        return [
            'source_type' => SourceType::INCIDENT->value,
            'source_id' => $incident->id,
            'ai_model_id' => AiModel::factory(),
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
            'owner_team' => $this->faker->randomElement([
                OwnerTeam::PRODUCT_OPS->value,
                OwnerTeam::ENGINEERING->value,
                OwnerTeam::DATA_SCIENCE->value,
                OwnerTeam::SECURITY->value,
                OwnerTeam::PRIVACY->value,
                OwnerTeam::RISK->value,
                OwnerTeam::LEGAL->value,
                OwnerTeam::VENDOR_MGMT->value,
            ]),
            'assignee' => $this->faker->name(),
            'root_cause' => $this->faker->paragraph(),
            'actions' => $this->faker->paragraphs(2, true),
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'status' => Status::NEW->value,
            'verification_result' => null,
            'evidence_link' => null,
            'closed_at' => null,
        ];
    }

    /**
     * Indicate that the action is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Status::IN_PROGRESS->value,
        ]);
    }

    /**
     * Indicate that the action is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Status::BLOCKED->value,
        ]);
    }

    /**
     * Indicate that the action is pending verification.
     */
    public function pendingVerification(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Status::PENDING_VERIFICATION->value,
            'verification_result' => VerificationResult::PENDING->value,
        ]);
    }

    /**
     * Indicate that the action is closed.
     */
    public function closed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Status::CLOSED->value,
            'verification_result' => $this->faker->randomElement([
                VerificationResult::PASSED->value,
                VerificationResult::FAILED->value,
                VerificationResult::NOT_APPLICABLE->value,
            ]),
            'evidence_link' => $this->faker->url(),
            'closed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the action is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => Priority::HIGH->value,
        ]);
    }

    /**
     * Indicate that the action is critical priority.
     */
    public function critical(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => Priority::CRITICAL->value,
        ]);
    }

    /**
     * Indicate that the action is corrective.
     */
    public function corrective(): static
    {
        return $this->state(fn(array $attributes) => [
            'capa_type' => CapaType::CORRECTIVE->value,
        ]);
    }

    /**
     * Indicate that the action is preventive.
     */
    public function preventive(): static
    {
        return $this->state(fn(array $attributes) => [
            'capa_type' => CapaType::PREVENTIVE->value,
        ]);
    }

    /**
     * Indicate that the action is from a risk source.
     */
    public function fromRisk(): static
    {
        return $this->state(fn(array $attributes) => [
            'source_type' => SourceType::RISK->value,
            'source_id' => $this->faker->numberBetween(1, 1000),
        ]);
    }

    /**
     * Indicate that the action is from an audit.
     */
    public function fromAudit(): static
    {
        return $this->state(fn(array $attributes) => [
            'source_type' => SourceType::AUDIT->value,
            'source_id' => $this->faker->numberBetween(1, 1000),
        ]);
    }
}
