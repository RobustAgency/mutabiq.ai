<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Organization;
use App\Models\PrivacyIncident;
use App\Enums\PrivacyIncident\Status;
use App\Enums\PrivacyIncident\RiskLevel;
use App\Enums\PrivacyIncident\IncidentType;
use App\Enums\PrivacyIncident\NotificationMethod;
use App\Enums\PrivacyIncident\NotificationStatus;
use App\Enums\PrivacyIncident\NotificationRequired;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\RecordOfProcessingActivity\DataCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PrivacyIncident>
 */
class PrivacyIncidentFactory extends Factory
{
    protected $model = PrivacyIncident::class;

    public function definition(): array
    {
        $detectedDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $notificationDeadline = $this->faker->dateTimeBetween($detectedDate, '+5 days');
        $isDeadlinePassed = now() > $notificationDeadline;

        return [
            'organization_id' => Organization::factory(),
            'incident_code' => 'INC-'.strtoupper($this->faker->unique()->bothify('????-####')),
            'incident_title' => $this->faker->sentence(),
            'incident_type' => $this->faker->randomElement(IncidentType::cases())->value,
            'risk_level' => $this->faker->randomElement(RiskLevel::cases())->value,
            'is_breach' => $this->faker->boolean(40),
            'breach_criteria_met' => $this->faker->boolean(50) ? ['high_risk', 'special_categories'] : null,
            'detected_date' => $detectedDate,
            'occurred_date' => $this->faker->dateTimeBetween('-60 days', $detectedDate),
            'notification_deadline' => $notificationDeadline,
            'hours_to_deadline' => $this->faker->numberBetween(1, 72),
            'is_deadline_passed' => $isDeadlinePassed,
            'incident_description' => $this->faker->paragraph(),
            'what_happened' => $this->faker->paragraph(),
            'how_discovered' => $this->faker->paragraph(),
            'data_compromised' => $this->faker->paragraph(),
            'data_categories_affected' => $this->faker->randomElements(
                [DataCategory::NAME->value, DataCategory::CONTACT->value, DataCategory::FINANCIAL->value, DataCategory::HEALTH->value],
                $this->faker->numberBetween(1, 3)
            ),
            'estimated_affected_subjects' => $this->faker->numberBetween(10, 10000),
            'affected_subject_keys' => $this->faker->boolean(60) ? $this->faker->randomElements(
                array_map(fn () => $this->faker->uuid(), range(1, 5)),
                $this->faker->numberBetween(1, 5)
            ) : null,
            'notification_required' => $this->faker->randomElement(NotificationRequired::cases())->value,
            'notification_status' => $this->faker->randomElement(NotificationStatus::cases())->value,
            'authority_notified' => $this->faker->boolean(60),
            'authority_notification_date' => $this->faker->boolean(60) ? $this->faker->dateTime() : null,
            'supervisory_authority' => $this->faker->boolean(60) ? $this->faker->word() : null,
            'authority_reference_number' => $this->faker->boolean(60) ? $this->faker->regexify('[A-Z]{3}[0-9]{6}') : null,
            'authority_response' => $this->faker->boolean(30) ? $this->faker->paragraph() : null,
            'subjects_notified' => $this->faker->boolean(50),
            'subject_notification_date' => $this->faker->boolean(50) ? $this->faker->dateTime() : null,
            'notification_method' => $this->faker->boolean(50) ? $this->faker->randomElement(NotificationMethod::cases())->value : null,
            'notification_template_used' => $this->faker->boolean(40) ? $this->faker->word() : null,
            'immediate_actions' => $this->faker->paragraph(),
            'mitigation_measures' => $this->faker->paragraph(),
            'preventive_measures' => $this->faker->paragraph(),
            'root_cause_analysis' => $this->faker->boolean(50) ? $this->faker->paragraph() : null,
            'responsible_party' => $this->faker->boolean(60) ? $this->faker->name() : null,
            'lessons_learned' => $this->faker->boolean(40) ? $this->faker->paragraph() : null,
            'status' => $this->faker->randomElement(Status::cases())->value,
            'resolution_date' => $this->faker->boolean(40) ? $this->faker->dateTime() : null,
            'days_to_resolution' => $this->faker->boolean(40) ? $this->faker->numberBetween(1, 90) : null,
            'processing_activity_ids' => $this->faker->boolean(60) ? $this->faker->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 3)) : null,
            'affected_systems' => $this->faker->randomElements(
                ['CRM', 'ERP', 'Database', 'File Server', 'Email', 'Cloud Storage'],
                $this->faker->numberBetween(1, 3)
            ),
            'third_party_involved' => $this->faker->boolean(40),
            'vendor_id' => $this->faker->boolean(40) ? Vendor::factory() : null,
            'evidence_uris' => $this->faker->boolean(60) ? array_map(fn () => $this->faker->url(), range(1, 2)) : null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * State for a breach incident
     */
    public function breach(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_breach' => true,
            'breach_criteria_met' => ['high_risk', 'special_categories'],
            'authority_notified' => true,
            'subjects_notified' => true,
        ]);
    }

    /**
     * State for a resolved incident
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::RESOLVED->value,
            'resolution_date' => $this->faker->dateTime(),
            'days_to_resolution' => $this->faker->numberBetween(1, 90),
            'root_cause_analysis' => $this->faker->paragraph(),
            'lessons_learned' => $this->faker->paragraph(),
        ]);
    }

    /**
     * State for a high-risk incident
     */
    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => RiskLevel::HIGH->value,
            'estimated_affected_subjects' => $this->faker->numberBetween(1000, 10000),
        ]);
    }

    /**
     * State for a low-risk incident
     */
    public function lowRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => RiskLevel::LOW->value,
            'estimated_affected_subjects' => $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * State for an incident under investigation
     */
    public function underInvestigation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::UNDER_INVESTIGATION->value,
        ]);
    }
}
