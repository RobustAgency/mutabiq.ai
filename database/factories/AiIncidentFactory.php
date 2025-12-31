<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\Dataset;
use App\Models\AiIncident;
use App\Models\Organization;
use App\Enums\AiIncident\Domain;
use App\Enums\AiIncident\IncidentType;
use App\Enums\AiIncident\ResponseTeam;
use App\Enums\AiIncident\ExternalParty;
use App\Enums\AiIncident\IncidentStatus;
use App\Enums\AiIncident\ImpactedDataType;
use App\Enums\AiIncident\IncidentSeverity;
use App\Enums\AiIncident\ResidencyAffected;
use App\Enums\AiIncident\AffectedBusinessUnit;
use App\Enums\AiIncident\NotificationRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\AiIncident\PrimaryRegulatoryFramework;

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
        // Select 1-3 impacted data types
        $impactedDataTypes = $this->faker->randomElements(
            array_map(fn ($case) => $case->value, ImpactedDataType::cases()),
            $this->faker->numberBetween(1, 3)
        );

        // Select 0-2 affected business units
        $affectedBusinessUnits = $this->faker->boolean(70) ? $this->faker->randomElements(
            array_map(fn ($case) => $case->value, AffectedBusinessUnit::cases()),
            $this->faker->numberBetween(1, 2)
        ) : [];

        // Select 0-1 external parties
        $externalParties = $this->faker->boolean(40) ? $this->faker->randomElements(
            array_map(fn ($case) => $case->value, ExternalParty::cases()),
            $this->faker->numberBetween(1, 2)
        ) : [];

        return [
            'organization_id' => Organization::factory(),
            'title' => $this->faker->sentence(6),
            'summary' => $this->faker->paragraph(3),
            'incident_type' => $this->faker->randomElement(IncidentType::cases())->value,
            'domain' => $this->faker->randomElement(Domain::cases())->value,
            'severity' => $this->faker->randomElement(IncidentSeverity::cases())->value,
            'status' => $this->faker->randomElement(IncidentStatus::cases())->value,
            'incident_commander' => $this->faker->name(),
            'response_team' => $this->faker->randomElement(ResponseTeam::cases())->value,
            'primary_regulatory_framework' => $this->faker->randomElement(PrimaryRegulatoryFramework::cases())->value,
            'notification_requirement' => $this->faker->randomElement(NotificationRequirement::cases())->value,
            'data_residency_affected' => $this->faker->boolean(70) ? $this->faker->randomElement(ResidencyAffected::cases())->value : null,
            'regulatory_reference' => $this->faker->boolean(50) ? $this->faker->sentence(4) : null,
            'estimated_impacted_users' => $this->faker->boolean(70) ? $this->faker->numberBetween(1, 100000) : null,
            'estimated_impacted_records' => $this->faker->numberBetween(1, 500000),
            'data_types_impacted' => $impactedDataTypes,
            'affected_business_units' => $affectedBusinessUnits,
            'external_parties_involved' => $externalParties,
            'business_impact_description' => $this->faker->boolean(60) ? $this->faker->paragraph(2) : null,
            'impacted_systems' => $this->faker->boolean(60) ? $this->faker->sentence(10) : null,
            'ai_model_id' => $this->faker->boolean(70) ? AiModel::factory() : null,
            'linked_dataset_id' => $this->faker->boolean(30) ? Dataset::factory() : null,
            'linked_risk_id' => $this->faker->boolean(40) ? $this->faker->numberBetween(1, 1000) : null,
            'evidence_link' => $this->faker->boolean(50) ? $this->faker->url() : null,
        ];
    }

    /**
     * Indicate that the incident is critical.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => IncidentSeverity::SEV1_CRITICAL->value,
            'status' => IncidentStatus::OPEN->value,
        ]);
    }

    /**
     * Indicate that the incident is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncidentStatus::RESOLVED->value,
        ]);
    }

    /**
     * Indicate that the incident is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncidentStatus::CLOSED->value,
        ]);
    }

    /**
     * Indicate that the incident is a data privacy incident.
     */
    public function dataPrivacy(): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => Domain::DATA_PRIVACY->value,
            'incident_type' => IncidentType::PRIVACY_VIOLATION->value,
            'primary_regulatory_framework' => PrimaryRegulatoryFramework::GDPR->value,
            'response_team' => ResponseTeam::DATA_PRIVACY_OFFICE->value,
        ]);
    }

    /**
     * Indicate that the incident is a security incident.
     */
    public function security(): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => Domain::INFORMATION_SECURITY->value,
            'incident_type' => IncidentType::SECURITY_INCIDENT->value,
            'response_team' => ResponseTeam::INFORMATION_SECURITY->value,
        ]);
    }

    /**
     * Indicate that the incident is an AI-related incident.
     */
    public function aiGovernance(): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => Domain::AI_GOVERNANCE->value,
            'incident_type' => IncidentType::AI_MODEL_FAILURE->value,
            'response_team' => ResponseTeam::ML_ENGINEERING->value,
        ]);
    }
}
