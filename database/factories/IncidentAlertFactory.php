<?php

namespace Database\Factories;

use App\Models\AiIncident;
use App\Models\DataSource;
use App\Enums\IncidentAlert\AlertSeverity;
use App\Enums\IncidentAlert\AlertSourceType;
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
            'organization_id' => \App\Models\Organization::factory(),
            'ai_incident_id' => AiIncident::factory(),
            'source_type' => $this->faker->randomElement(array_map(fn ($c) => $c->value, AlertSourceType::cases())),
            'data_source_id' => DataSource::factory(),
            'source_ref' => $this->faker->optional(0.6)->regexify('[A-Z]{3}-[0-9]{3,6}'),
            'alert_sensitivity' => $this->faker->randomElement(array_map(fn ($c) => $c->value, AlertSeverity::cases())),
            'context' => $this->faker->sentence(20),
            'first_seen_at' => $firstSeenAt,
            'last_seen_at' => $lastSeenAt,
            'auto_promote_incident' => $this->faker->boolean(20),
            'evidence_link' => $this->faker->optional(0.6)->url(),
        ];
    }

    /**
     * Indicate that the alert is from a KRI threshold.
     */
    public function fromKriThreshold(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => AlertSourceType::KRI_THRESHOLD->value,
            'source_ref' => 'KRI-'.$this->faker->numberBetween(100, 999),
            'alert_sensitivity' => AlertSeverity::HIGH->value,
        ]);
    }

    /**
     * Indicate that the alert is from a monitoring rule.
     */
    public function fromMonitoringRule(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => AlertSourceType::MONITORING_RULE->value,
            'source_ref' => 'RULE-'.$this->faker->numberBetween(1000, 9999),
            'alert_sensitivity' => AlertSeverity::MEDIUM->value,
        ]);
    }

    /**
     * Indicate that the alert is from a manual report.
     */
    public function fromManualReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => AlertSourceType::MANUAL_REPORT->value,
            'source_ref' => 'TICKET-'.$this->faker->numberBetween(10000, 99999),
            'alert_sensitivity' => AlertSeverity::MEDIUM->value,
        ]);
    }

    /**
     * Indicate that the alert is from an automated scan.
     */
    public function fromAutomatedScan(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => AlertSourceType::AUTOMATED_SCAN->value,
            'source_ref' => 'SCAN-'.$this->faker->numberBetween(10000, 99999),
            'alert_sensitivity' => AlertSeverity::MEDIUM->value,
        ]);
    }

    /**
     * Indicate that the alert is from a user complaint.
     */
    public function fromUserComplaint(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => AlertSourceType::USER_COMPLAINT->value,
            'source_ref' => 'COMPLAINT-'.$this->faker->numberBetween(1000, 9999),
            'alert_sensitivity' => AlertSeverity::LOW->value,
        ]);
    }

    /**
     * Indicate that the alert is from an external report.
     */
    public function fromExternalReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => AlertSourceType::EXTERNAL_REPORT->value,
            'source_ref' => 'EXT-'.$this->faker->numberBetween(10000, 99999),
            'alert_sensitivity' => AlertSeverity::HIGH->value,
        ]);
    }

    /**
     * Indicate that the alert has recent activity.
     */
    public function recent(): static
    {
        $firstSeenAt = now()->subHours($this->faker->numberBetween(1, 6));

        return $this->state(fn (array $attributes) => [
            'first_seen_at' => $firstSeenAt,
            'last_seen_at' => now()->subMinutes($this->faker->numberBetween(5, 60)),
        ]);
    }

    /**
     * Indicate that the alert should auto-promote to incident.
     */
    public function autoPromote(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_promote_incident' => true,
        ]);
    }

    /**
     * Indicate that the alert is critical.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_sensitivity' => AlertSeverity::CRITICAL->value,
        ]);
    }
}
