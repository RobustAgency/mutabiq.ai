<?php

namespace Database\Factories;

use App\Models\AiIncident;
use App\Enums\AlertSourceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IncidentAlert>
 */
class IncidentAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstSeenAt = $this->faker->dateTimeBetween('-7 days', '-1 day');
        $lastSeenAt = $this->faker->optional(0.7)->dateTimeBetween($firstSeenAt, 'now');

        return [
            'ai_incident_id' => AiIncident::factory(),
            'source_type' => $this->faker->randomElement(array_map(fn($c) => $c->value, AlertSourceType::cases())),
            'source_ref' => $this->faker->optional(0.6)->regexify('[A-Z]{3}-[0-9]{3,6}'),
            'rule_version' => $this->faker->optional(0.5)->regexify('v[0-9]\.[0-9]\.[0-9]'),
            'context' => $this->faker->optional(0.7)->sentence(20),
            'first_seen_at' => $firstSeenAt,
            'last_seen_at' => $lastSeenAt,
            'evidence_link' => $this->faker->optional(0.6)->url(),
        ];
    }

    /**
     * Indicate that the alert is from a KRI source.
     */
    public function fromKri(): static
    {
        return $this->state(fn(array $attributes) => [
            'source_type' => AlertSourceType::KRI->value,
            'source_ref' => 'KRI-' . $this->faker->numberBetween(100, 999),
        ]);
    }

    /**
     * Indicate that the alert is from a monitoring rule.
     */
    public function fromMonitoringRule(): static
    {
        return $this->state(fn(array $attributes) => [
            'source_type' => AlertSourceType::MONITORING_RULE->value,
            'source_ref' => 'RULE-' . $this->faker->numberBetween(1000, 9999),
            'rule_version' => 'v' . $this->faker->numberBetween(1, 5) . '.' . $this->faker->numberBetween(0, 9) . '.0',
        ]);
    }

    /**
     * Indicate that the alert is from a human report.
     */
    public function fromHumanReport(): static
    {
        return $this->state(fn(array $attributes) => [
            'source_type' => AlertSourceType::HUMAN_REPORT->value,
            'source_ref' => 'TICKET-' . $this->faker->numberBetween(10000, 99999),
        ]);
    }

    /**
     * Indicate that the alert has recent activity.
     */
    public function recent(): static
    {
        $firstSeenAt = now()->subHours($this->faker->numberBetween(1, 6));

        return $this->state(fn(array $attributes) => [
            'first_seen_at' => $firstSeenAt,
            'last_seen_at' => now()->subMinutes($this->faker->numberBetween(5, 60)),
        ]);
    }
}
