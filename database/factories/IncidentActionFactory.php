<?php

namespace Database\Factories;

use App\Enums\IncidentAction\ActionType;
use App\Enums\IncidentAction\ValidationResult;
use App\Models\AiIncident;
use App\Models\IncidentAction;
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
            'ai_incident_id' => AiIncident::factory(),
            'action_type' => $this->faker->randomElement(ActionType::cases())->value,
            'description' => $this->faker->paragraph(2),
            'performed_by' => $this->faker->name(),
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'validation_result' => $this->faker->randomElement(ValidationResult::cases())->value,
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
        return $this->state(fn(array $attributes) => [
            'action_type' => ActionType::KILL_SWITCH->value,
        ]);
    }

    /**
     * Indicate that the action is completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'completed_at' => now(),
            'validation_result' => ValidationResult::PASSED->value,
        ]);
    }

    /**
     * Indicate that the validation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'validation_result' => ValidationResult::PENDING->value,
        ]);
    }
}
