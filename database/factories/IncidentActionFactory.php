<?php

namespace Database\Factories;

use App\Models\AiIncident;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\IncidentAction;
use App\Enums\IncidentAction\ActionType;
use App\Enums\IncidentAction\ExecutionStatus;
use App\Enums\IncidentAction\ApprovalRequired;
use App\Enums\IncidentAction\ValidationResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncidentAction>
 */
class IncidentActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-30 days', '-1 day');
        $hasCompleted = $this->faker->boolean(70);
        $completedAt = $hasCompleted ? $this->faker->dateTimeBetween($startedAt, 'now') : null;

        return [
            'organization_id' => Organization::factory(),
            'ai_incident_id' => AiIncident::factory(),
            'action_type' => $this->faker->randomElement(array_map(fn ($c) => $c->value, ActionType::cases())),
            'execution_status' => $this->faker->randomElement(array_map(fn ($c) => $c->value, ExecutionStatus::cases())),
            'description' => $this->faker->paragraph(2),
            'performed_by' => Stakeholder::factory(),
            'individual_name' => $this->faker->boolean(40) ? $this->faker->name() : null,
            'depends_on' => $this->faker->boolean(20) ? $this->faker->sentence(3) : null,
            'approval_required' => $this->faker->boolean(30) ? $this->faker->randomElement(array_map(fn ($c) => $c->value, ApprovalRequired::cases())) : null,
            'estimated_duration' => $this->faker->boolean(50) ? $this->faker->numberBetween(15, 480) : null,
            'actual_duration' => $hasCompleted ? $this->faker->numberBetween(10, 600) : null,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'validation_result' => $this->faker->randomElement(array_map(fn ($c) => $c->value, ValidationResult::cases())),
            'validation_notes' => $this->faker->boolean(60) ? $this->faker->paragraph(2) : null,
            'linked_release_id' => $this->faker->boolean(30) ? $this->faker->numerify('REL-####') : null,
            'evidence_link' => $this->faker->boolean(50) ? $this->faker->url() : null,
        ];
    }

    /**
     * Indicate that the action is a kill switch.
     */
    public function killSwitch(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => ActionType::KILL_SWITCH->value,
            'execution_status' => ExecutionStatus::COMPLETED->value,
        ]);
    }

    /**
     * Indicate that the action is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_status' => ExecutionStatus::COMPLETED->value,
            'completed_at' => now(),
            'validation_result' => ValidationResult::EFFECTIVE->value,
        ]);
    }

    /**
     * Indicate that the execution status is planned.
     */
    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_status' => ExecutionStatus::PLANNED->value,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the execution status is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_status' => ExecutionStatus::IN_PROGRESS->value,
        ]);
    }

    /**
     * Indicate that the execution status is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_status' => ExecutionStatus::FAILED->value,
            'validation_result' => ValidationResult::INEFFECTIVE->value,
        ]);
    }

    /**
     * Indicate that the execution status is rolled back.
     */
    public function rolledBack(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_status' => ExecutionStatus::ROLLED_BACK->value,
        ]);
    }
}
