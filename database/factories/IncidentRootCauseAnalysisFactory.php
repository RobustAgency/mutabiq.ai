<?php

namespace Database\Factories;

use App\Enums\IncidentRootCauseAnalysis\RcaMethod;
use App\Models\AiIncident;
use App\Models\IncidentRootCauseAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncidentRootCauseAnalysis>
 */
class IncidentRootCauseAnalysisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $approvedAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'organization_id' => \App\Models\Organization::factory(),
            'ai_incident_id' => AiIncident::factory(),
            'rca_method' => $this->faker->randomElement(RcaMethod::cases())->value,
            'immediate_cause' => $this->faker->paragraph(2),
            'latent_causes' => $this->faker->paragraph(3),
            'contributing_factors' => $this->faker->boolean(60) ? $this->faker->paragraph(2) : null,
            'impact_assessment' => $this->faker->boolean(60) ? $this->faker->paragraph(2) : null,
            'fixes_implemented' => $this->faker->boolean(70) ? $this->faker->paragraph(2) : null,
            'lessons_learned' => $this->faker->paragraph(3),
            'recommendations' => $this->faker->paragraph(3),
            'approved_by' => $this->faker->name(),
            'approved_at' => $approvedAt,
            'report_link' => $this->faker->boolean(50) ? $this->faker->url() : null,
        ];
    }

    /**
     * Indicate that the RCA uses the 5 whys method.
     */
    public function fiveWhys(): static
    {
        return $this->state(fn(array $attributes) => [
            'rca_method' => RcaMethod::FIVE_WHYS->value,
        ]);
    }

    /**
     * Indicate that the RCA uses the fishbone method.
     */
    public function fishbone(): static
    {
        return $this->state(fn(array $attributes) => [
            'rca_method' => RcaMethod::FISHBONE->value,
        ]);
    }

    /**
     * Indicate that the RCA has all optional fields filled.
     */
    public function complete(): static
    {
        return $this->state(fn(array $attributes) => [
            'contributing_factors' => $this->faker->paragraph(2),
            'impact_assessment' => $this->faker->paragraph(2),
            'fixes_implemented' => $this->faker->paragraph(2),
            'report_link' => $this->faker->url(),
        ]);
    }
}
