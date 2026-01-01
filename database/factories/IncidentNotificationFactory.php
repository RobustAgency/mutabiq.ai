<?php

namespace Database\Factories;

use App\Models\AiIncident;
use App\Models\IncidentNotification;
use App\Enums\IncidentNotification\Channel;
use App\Enums\IncidentNotification\Language;
use App\Enums\IncidentNotification\Template;
use App\Enums\IncidentNotification\AudienceType;
use App\Enums\IncidentNotification\DeliveryStatus;
use App\Enums\IncidentNotification\RegulatoryBasis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncidentNotification>
 */
class IncidentNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $audienceType = $this->faker->randomElement(AudienceType::cases());
        $sentAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'organization_id' => \App\Models\Organization::factory(),
            'ai_incident_id' => AiIncident::factory(),
            'template' => $this->faker->randomElement(Template::cases())->value,
            'language' => $this->faker->randomElement(Language::cases())->value,
            'regulatory_basis' => $this->faker->boolean(60) ? $this->faker->randomElement(RegulatoryBasis::cases())->value : null,
            'notification_deadline' => $this->faker->dateTimeBetween('+1 days', '+90 days'),
            'audience_type' => $audienceType->value,
            'channel' => $this->faker->randomElement(Channel::cases())->value,
            'notice_summary' => $this->faker->paragraph(3),
            'notice_link' => $this->faker->boolean(50) ? $this->faker->url() : null,
            'sent_at' => $sentAt,
            'sent_by' => $this->faker->boolean(70) ? $this->faker->name() : null,
            'delivery_status' => $this->faker->randomElement(DeliveryStatus::cases())->value,
            'response_summary' => $this->faker->boolean(50) ? $this->faker->paragraph() : null,
            'follow_up_required' => $this->faker->boolean(30),
            'follow_up_date' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('+1 days', '+30 days') : null,
            'follow_up_notes' => $this->faker->boolean(30) ? $this->faker->paragraph() : null,
        ];
    }

    /**
     * Indicate that the notification is for customers.
     */
    public function forCustomers(): static
    {
        return $this->state(fn (array $attributes) => [
            'audience_type' => AudienceType::AFFECTED_DATA_SUBJECTS->value,
            'channel' => Channel::EMAIL->value,
            'delivery_status' => DeliveryStatus::SENT->value,
        ]);
    }

    /**
     * Indicate that the notification is for regulators.
     */
    public function forRegulator(): static
    {
        return $this->state(fn (array $attributes) => [
            'audience_type' => AudienceType::DATA_PROTECTION_AUTHORITY->value,
            'channel' => Channel::FORMAL_LETTER->value,
            'regulatory_basis' => RegulatoryBasis::GDPR_ART_33->value,
            'delivery_status' => DeliveryStatus::SENT->value,
        ]);
    }

    /**
     * Indicate that the notification requires follow-up.
     */
    public function requiresFollowUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'follow_up_required' => true,
            'follow_up_date' => $this->faker->dateTimeBetween('+1 days', '+30 days'),
            'follow_up_notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the notification is internal.
     */
    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'audience_type' => $this->faker->randomElement([
                AudienceType::INTERNAL_EXECUTIVE->value,
                AudienceType::INTERNAL_TECHNICAL->value,
            ]),
            'delivery_status' => DeliveryStatus::SENT->value,
        ]);
    }

    /**
     * Indicate that the notification delivery failed.
     */
    public function deliveryFailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_status' => DeliveryStatus::FAILED->value,
            'follow_up_required' => true,
        ]);
    }

    /**
     * Indicate that the notification is in draft state.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_status' => DeliveryStatus::DRAFT->value,
        ]);
    }
}
