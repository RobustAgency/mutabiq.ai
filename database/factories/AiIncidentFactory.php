<?php

namespace Database\Factories;

use App\Enums\ImpactedDataType;
use App\Enums\IncidentCategory;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStage;
use App\Enums\IncidentStatus;
use App\Models\AiIncident;
use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\UseCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiIncident>
 */
class AiIncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstSeenAt = $this->faker->dateTimeBetween('-30 days', '-1 day');
        $declaredAt = $this->faker->dateTimeBetween($firstSeenAt, 'now');

        $hasResolved = $this->faker->boolean(60);
        $resolvedAt = $hasResolved ? $this->faker->dateTimeBetween($declaredAt, 'now') : null;

        $hasClosed = $hasResolved && $this->faker->boolean(70);
        $closedAt = $hasClosed ? $this->faker->dateTimeBetween($resolvedAt, 'now') : null;

        // Select 1-3 impacted data types
        $impactedDataTypes = $this->faker->randomElements(
            array_map(fn($case) => $case->value, ImpactedDataType::cases()),
            $this->faker->numberBetween(1, 3)
        );

        return [
            'title' => $this->faker->sentence(6),
            'summary' => $this->faker->paragraph(3),
            'category' => $this->faker->randomElement(IncidentCategory::cases())->value,
            'severity' => $this->faker->randomElement(IncidentSeverity::cases())->value,
            'status' => $this->faker->randomElement(IncidentStatus::cases())->value,
            'stage' => $this->faker->randomElement(IncidentStage::cases())->value,
            'ic_owner' => $this->faker->name(),
            'ai_model_id' => AiModel::factory(),
            'ai_model_version_id' => AiModelVersion::factory(),
            'use_case_id' => UseCase::factory(),
            'first_seen_at' => $firstSeenAt,
            'declared_at' => $declaredAt,
            'resolved_at' => $resolvedAt,
            'closed_at' => $closedAt,
            'impacted_users' => $this->faker->boolean(70) ? $this->faker->randomElement([
                'internal only',
                '< 100 users',
                '100-1000 users',
                '1000+ users',
                $this->faker->numberBetween(1, 10000) . ' users',
            ]) : null,
            'impacted_data' => $impactedDataTypes,
            'impacted_systems' => $this->faker->boolean(60) ? $this->faker->sentence(10) : null,
            'linked_release_id' => $this->faker->boolean(30) ? $this->faker->numberBetween(1, 100) : null,
            'linked_risk_id' => $this->faker->boolean(40) ? $this->faker->numberBetween(1, 100) : null,
            'linked_assessment_id' => $this->faker->boolean(30) ? $this->faker->numberBetween(1, 100) : null,
            'linked_capa_id' => $this->faker->boolean(30) ? $this->faker->numberBetween(1, 100) : null,
            'evidence_link' => $this->faker->boolean(50) ? $this->faker->url() : null,
        ];
    }

    /**
     * Indicate that the incident is critical.
     */
    public function critical(): static
    {
        return $this->state(fn(array $attributes) => [
            'severity' => IncidentSeverity::SEV1_CRITICAL->value,
            'status' => IncidentStatus::OPEN->value,
        ]);
    }

    /**
     * Indicate that the incident is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => IncidentStatus::RESOLVED->value,
            'resolved_at' => now()->subDays($this->faker->numberBetween(1, 7)),
        ]);
    }

    /**
     * Indicate that the incident is closed.
     */
    public function closed(): static
    {
        $resolvedAt = now()->subDays($this->faker->numberBetween(7, 14));

        return $this->state(fn(array $attributes) => [
            'status' => IncidentStatus::CLOSED->value,
            'resolved_at' => $resolvedAt,
            'closed_at' => $this->faker->dateTimeBetween($resolvedAt, 'now'),
        ]);
    }

    /**
     * Indicate that the incident is in production.
     */
    public function production(): static
    {
        return $this->state(fn(array $attributes) => [
            'stage' => IncidentStage::PROD->value,
        ]);
    }
}
