<?php

namespace Database\Factories;

use App\Enums\IncidentNotification\AudienceType;
use App\Enums\IncidentNotification\Channel;
use App\Models\AiIncident;
use App\Models\IncidentNotification;
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
        $audienceType = $this->faker->randomElement(AudienceType::cases())->value;
        $isExternalAudience = in_array($audienceType, ['customers', 'regulator', 'vendor', 'media']);
        $notifiedAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'organization_id' => \App\Models\Organization::factory(),
            'ai_incident_id' => AiIncident::factory(),
            'audience_type' => $audienceType,
            'channel' => $this->faker->randomElement(Channel::cases())->value,
            'notice_summary' => $this->faker->paragraph(3),
            'notice_link' => $this->faker->boolean(50) ? $this->faker->url() : null,
            'notified_at' => $notifiedAt,
            'approved_by' => $isExternalAudience || $this->faker->boolean(50) ? $this->faker->name() : null,
            'approval_ref' => $this->faker->boolean(40) ? $this->faker->bothify('APPR-####') : null,
            'follow_up_required' => $this->faker->boolean(30),
        ];
    }

    /**
     * Indicate that the notification is for customers.
     */
    public function forCustomers(): static
    {
        return $this->state(fn(array $attributes) => [
            'audience_type' => AudienceType::CUSTOMERS->value,
            'approved_by' => $this->faker->name(),
        ]);
    }

    /**
     * Indicate that the notification is for regulators.
     */
    public function forRegulator(): static
    {
        return $this->state(fn(array $attributes) => [
            'audience_type' => AudienceType::REGULATOR->value,
            'channel' => Channel::LEGAL_LETTER->value,
            'approved_by' => $this->faker->name(),
            'approval_ref' => $this->faker->bothify('REG-####'),
        ]);
    }

    /**
     * Indicate that the notification requires follow-up.
     */
    public function requiresFollowUp(): static
    {
        return $this->state(fn(array $attributes) => [
            'follow_up_required' => true,
        ]);
    }

    /**
     * Indicate that the notification is internal.
     */
    public function internal(): static
    {
        return $this->state(fn(array $attributes) => [
            'audience_type' => $this->faker->randomElement([
                AudienceType::INTERNAL_EXEC->value,
                AudienceType::INTERNAL_STAFF->value,
            ]),
        ]);
    }
}
