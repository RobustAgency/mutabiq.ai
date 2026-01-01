<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\AiIncident;
use App\Models\Organization;
use App\Models\IncidentNotification;
use App\Enums\IncidentNotification\Channel;
use App\Enums\IncidentNotification\Language;
use App\Enums\IncidentNotification\Template;
use App\Enums\IncidentNotification\AudienceType;
use App\Enums\IncidentNotification\DeliveryStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\IncidentNotification\RegulatoryBasis;
use App\Repositories\IncidentNotificationRepository;

class IncidentNotificationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected IncidentNotificationRepository $repository;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new IncidentNotificationRepository;
        $this->organization = Organization::factory()->create();
    }

    public function test_paginate_returns_paginated_incident_notifications(): void
    {
        IncidentNotification::factory()->count(15)->create();

        $result = $this->repository->getFilteredIncidentNotifications(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_paginate_with_default_per_page(): void
    {
        IncidentNotification::factory()->count(5)->create();

        $result = $this->repository->getFilteredIncidentNotifications();

        $this->assertCount(5, $result->items());
    }

    public function test_create_stores_incident_notification_with_required_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'regulatory_basis' => RegulatoryBasis::GDPR_ART_33->value,
            'notice_summary' => 'Test notification summary',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertInstanceOf(IncidentNotification::class, $notification);
        $this->assertEquals($incident->id, $notification->ai_incident_id);
        $this->assertEquals(AudienceType::INTERNAL_TECHNICAL->value, $notification->audience_type);
        $this->assertEquals(Channel::EMAIL->value, $notification->channel);
        $this->assertEquals('Test notification summary', $notification->notice_summary);
        $this->assertFalse($notification->follow_up_required);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
        ]);
    }

    public function test_create_stores_incident_notification_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $sentAt = now()->subHours(3);
        $notificationDeadline = now()->addDays(7);
        $followUpDate = now()->addDays(14);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'template' => Template::CUSTOMER_NOTICE_TEMPLATE->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::AFFECTED_DATA_SUBJECTS->value,
            'channel' => Channel::EMAIL->value,
            'regulatory_basis' => RegulatoryBasis::GDPR_ART_34->value,
            'notification_deadline' => $notificationDeadline->toDateTimeString(),
            'notice_summary' => 'Customer-facing incident notification',
            'notice_link' => 'https://status.example.com/incident-456',
            'sent_at' => $sentAt->toDateTimeString(),
            'sent_by' => 'system@example.com',
            'delivery_status' => DeliveryStatus::DELIVERED->value,
            'response_summary' => 'All customers notified successfully',
            'follow_up_required' => true,
            'follow_up_date' => $followUpDate->toDateTimeString(),
            'follow_up_notes' => 'Monitor customer complaints',
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals(AudienceType::AFFECTED_DATA_SUBJECTS->value, $notification->audience_type);
        $this->assertEquals(Channel::EMAIL->value, $notification->channel);
        $this->assertEquals('https://status.example.com/incident-456', $notification->notice_link);
        $this->assertEquals('system@example.com', $notification->sent_by);
        $this->assertTrue($notification->follow_up_required);
    }

    public function test_create_handles_all_channel_types(): void
    {
        $incident = AiIncident::factory()->create();

        foreach (Channel::cases() as $channel) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'template' => Template::CUSTOM_OTHER->value,
                'language' => Language::ENGLISH->value,
                'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
                'channel' => $channel->value,
                'notice_summary' => "Notification via {$channel->value}",
                'sent_at' => now()->toDateTimeString(),
                'delivery_status' => DeliveryStatus::SENT->value,
                'follow_up_required' => false,
            ];

            $notification = $this->repository->createIncidentNotification($data);
            $this->assertEquals($channel->value, $notification->channel);
        }
    }

    public function test_create_handles_all_audience_types(): void
    {
        $incident = AiIncident::factory()->create();

        foreach (AudienceType::cases() as $audienceType) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'template' => Template::CUSTOM_OTHER->value,
                'language' => Language::ENGLISH->value,
                'audience_type' => $audienceType->value,
                'channel' => Channel::EMAIL->value,
                'notice_summary' => "Notification for {$audienceType->value}",
                'sent_at' => now()->toDateTimeString(),
                'delivery_status' => DeliveryStatus::SENT->value,
                'follow_up_required' => false,
            ];

            $notification = $this->repository->createIncidentNotification($data);
            $this->assertEquals($audienceType->value, $notification->audience_type);
        }
    }

    public function test_create_handles_all_delivery_statuses(): void
    {
        $incident = AiIncident::factory()->create();

        foreach (DeliveryStatus::cases() as $status) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'template' => Template::CUSTOM_OTHER->value,
                'language' => Language::ENGLISH->value,
                'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
                'channel' => Channel::EMAIL->value,
                'notice_summary' => "Notification with status {$status->value}",
                'sent_at' => now()->toDateTimeString(),
                'delivery_status' => $status->value,
                'follow_up_required' => false,
            ];

            $notification = $this->repository->createIncidentNotification($data);
            $this->assertEquals($status->value, $notification->delivery_status);
        }
    }

    public function test_create_handles_all_regulatory_basis_options(): void
    {
        $incident = AiIncident::factory()->create();

        foreach (RegulatoryBasis::cases() as $basis) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'template' => Template::DPA_BREACH_NOTIFICATION->value,
                'language' => Language::ENGLISH->value,
                'audience_type' => AudienceType::DATA_PROTECTION_AUTHORITY->value,
                'channel' => Channel::FORMAL_LETTER->value,
                'regulatory_basis' => $basis->value,
                'notice_summary' => "Notification under {$basis->value}",
                'sent_at' => now()->toDateTimeString(),
                'delivery_status' => DeliveryStatus::SENT->value,
                'follow_up_required' => false,
            ];

            $notification = $this->repository->createIncidentNotification($data);
            $this->assertEquals($basis->value, $notification->regulatory_basis);
        }
    }

    public function test_create_handles_all_templates(): void
    {
        $incident = AiIncident::factory()->create();

        foreach (Template::cases() as $template) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'template' => $template->value,
                'language' => Language::ENGLISH->value,
                'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
                'channel' => Channel::EMAIL->value,
                'notice_summary' => "Using template {$template->value}",
                'sent_at' => now()->toDateTimeString(),
                'delivery_status' => DeliveryStatus::SENT->value,
                'follow_up_required' => false,
            ];

            $notification = $this->repository->createIncidentNotification($data);
            $this->assertEquals($template->value, $notification->template);
        }
    }

    public function test_create_handles_all_languages(): void
    {
        $incident = AiIncident::factory()->create();

        foreach (Language::cases() as $language) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_incident_id' => $incident->id,
                'template' => Template::CUSTOM_OTHER->value,
                'language' => $language->value,
                'audience_type' => AudienceType::AFFECTED_DATA_SUBJECTS->value,
                'channel' => Channel::EMAIL->value,
                'notice_summary' => "Notification in {$language->value}",
                'sent_at' => now()->toDateTimeString(),
                'delivery_status' => DeliveryStatus::SENT->value,
                'follow_up_required' => false,
            ];

            $notification = $this->repository->createIncidentNotification($data);
            $this->assertEquals($language->value, $notification->language);
        }
    }

    public function test_find_by_id_returns_incident_notification(): void
    {
        $created = IncidentNotification::factory()->create();

        $notification = $this->repository->getIncidentNotificationById($created);

        $this->assertInstanceOf(IncidentNotification::class, $notification);
        $this->assertEquals($created->id, $notification->id);
    }

    public function test_update_modifies_incident_notification(): void
    {
        $notification = IncidentNotification::factory()->create([
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'follow_up_required' => false,
        ]);

        $updateData = [
            'audience_type' => AudienceType::INTERNAL_EXECUTIVE->value,
            'channel' => Channel::SLACK_TEAMS->value,
            'follow_up_required' => true,
            'notice_link' => 'https://example.com/meeting-notes',
        ];

        $updated = $this->repository->updateIncidentNotification($notification, $updateData);

        $this->assertEquals(AudienceType::INTERNAL_EXECUTIVE->value, $updated->audience_type);
        $this->assertEquals(Channel::SLACK_TEAMS->value, $updated->channel);
        $this->assertTrue($updated->follow_up_required);
        $this->assertEquals('https://example.com/meeting-notes', $updated->notice_link);
    }

    public function test_update_modifies_only_provided_fields(): void
    {
        $notification = IncidentNotification::factory()->create([
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Original summary',
        ]);

        $updateData = [
            'notice_summary' => 'Updated summary',
        ];

        $updated = $this->repository->updateIncidentNotification($notification, $updateData);

        $this->assertEquals(AudienceType::INTERNAL_TECHNICAL->value, $updated->audience_type);
        $this->assertEquals(Channel::EMAIL->value, $updated->channel);
        $this->assertEquals('Updated summary', $updated->notice_summary);
    }

    public function test_update_delivery_status_and_response(): void
    {
        $notification = IncidentNotification::factory()->create([
            'delivery_status' => DeliveryStatus::SENT->value,
            'response_summary' => null,
        ]);

        $updateData = [
            'delivery_status' => DeliveryStatus::DELIVERED->value,
            'response_summary' => 'Notification successfully delivered to all recipients',
        ];

        $updated = $this->repository->updateIncidentNotification($notification, $updateData);

        $this->assertEquals(DeliveryStatus::DELIVERED->value, $updated->delivery_status);
        $this->assertEquals('Notification successfully delivered to all recipients', $updated->response_summary);
    }

    public function test_update_follow_up_information(): void
    {
        $followUpDate = now()->addDays(7);

        $notification = IncidentNotification::factory()->create([
            'follow_up_required' => false,
            'follow_up_date' => null,
            'follow_up_notes' => null,
        ]);

        $updateData = [
            'follow_up_required' => true,
            'follow_up_date' => $followUpDate->toDateTimeString(),
            'follow_up_notes' => 'Monitor incident resolution and customer feedback',
        ];

        $updated = $this->repository->updateIncidentNotification($notification, $updateData);

        $this->assertTrue($updated->follow_up_required);
        $this->assertNotNull($updated->follow_up_date);
        $this->assertEquals('Monitor incident resolution and customer feedback', $updated->follow_up_notes);
    }

    public function test_delete_removes_incident_notification(): void
    {
        $notification = IncidentNotification::factory()->create();
        $id = $notification->id;

        $result = $this->repository->deleteIncidentNotification($notification);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('incident_notifications', ['id' => $id]);
    }

    public function test_paginate_loads_ai_incident_relationship(): void
    {
        IncidentNotification::factory()->count(3)->create();

        $result = $this->repository->getFilteredIncidentNotifications(['per_page' => 2]);

        $notification = $result->items()[0];
        $this->assertTrue($notification->relationLoaded('aiIncident'));
        $this->assertInstanceOf(AiIncident::class, $notification->aiIncident);
    }

    public function test_find_by_id_loads_ai_incident_relationship(): void
    {
        $created = IncidentNotification::factory()->create();

        $notification = $this->repository->getIncidentNotificationById($created);

        $this->assertTrue($notification->relationLoaded('aiIncident'));
        $this->assertInstanceOf(AiIncident::class, $notification->aiIncident);
    }

    public function test_filter_by_audience_type(): void
    {
        IncidentNotification::factory()->create(['audience_type' => AudienceType::INTERNAL_TECHNICAL->value]);
        IncidentNotification::factory()->create(['audience_type' => AudienceType::DATA_PROTECTION_AUTHORITY->value]);
        IncidentNotification::factory()->create(['audience_type' => AudienceType::AFFECTED_DATA_SUBJECTS->value]);

        $result = $this->repository->getFilteredIncidentNotifications([
            'audience_type' => AudienceType::DATA_PROTECTION_AUTHORITY->value,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals(AudienceType::DATA_PROTECTION_AUTHORITY->value, $result->items()[0]->audience_type);
    }

    public function test_filter_by_channel(): void
    {
        IncidentNotification::factory()->create(['channel' => Channel::EMAIL->value]);
        IncidentNotification::factory()->create(['channel' => Channel::SMS->value]);
        IncidentNotification::factory()->create(['channel' => Channel::FORMAL_LETTER->value]);

        $result = $this->repository->getFilteredIncidentNotifications([
            'channel' => Channel::SMS->value,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals(Channel::SMS->value, $result->items()[0]->channel);
    }

    public function test_filter_by_date_range(): void
    {
        $now = now();
        // Create records with explicit created_at dates
        IncidentNotification::factory()->create([
            'created_at' => $now->clone()->subDays(10),
        ]);
        IncidentNotification::factory()->create([
            'created_at' => $now->clone()->subDays(5),
        ]);
        IncidentNotification::factory()->create([
            'created_at' => $now->clone()->addDays(10),
        ]);

        $result = $this->repository->getFilteredIncidentNotifications([
            'from' => $now->clone()->subDays(7)->toDateString(),
            'to' => $now->clone()->toDateString(),
        ]);

        $this->assertCount(1, $result->items());
    }

    public function test_filter_by_organization(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        IncidentNotification::factory()->create(['organization_id' => $org1->id]);
        IncidentNotification::factory()->create(['organization_id' => $org2->id]);

        $result = $this->repository->getFilteredIncidentNotifications([
            'organization_id' => $org1->id,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals($org1->id, $result->items()[0]->organization_id);
    }

    public function test_create_complete_gdpr_breach_notification_scenario(): void
    {
        $incident = AiIncident::factory()->create();
        $sentAt = now()->subHours(2);
        $followUpDate = now()->addDays(30);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::DATA_PROTECTION_AUTHORITY->value,
            'channel' => Channel::FORMAL_LETTER->value,
            'regulatory_basis' => RegulatoryBasis::GDPR_ART_33->value,
            'notice_summary' => 'Formal notification of personal data breach to supervisory authority per GDPR Article 33.',
            'notice_link' => 'https://compliance.example.com/regulatory-submissions/BREACH-2024-OCT',
            'sent_at' => $sentAt->toDateTimeString(),
            'sent_by' => 'dpo@company.com',
            'delivery_status' => DeliveryStatus::DELIVERED->value,
            'response_summary' => 'Notification delivered to competent authority',
            'follow_up_required' => true,
            'follow_up_date' => $followUpDate->toDateTimeString(),
            'follow_up_notes' => 'Follow up on authority\'s investigation status in 30 days',
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals(AudienceType::DATA_PROTECTION_AUTHORITY->value, $notification->audience_type);
        $this->assertEquals(Channel::FORMAL_LETTER->value, $notification->channel);
        $this->assertEquals(RegulatoryBasis::GDPR_ART_33->value, $notification->regulatory_basis);
        $this->assertEquals(DeliveryStatus::DELIVERED->value, $notification->delivery_status);
        $this->assertTrue($notification->follow_up_required);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => AudienceType::DATA_PROTECTION_AUTHORITY->value,
            'regulatory_basis' => RegulatoryBasis::GDPR_ART_33->value,
        ]);
    }

    public function test_create_complete_customer_notification_scenario(): void
    {
        $incident = AiIncident::factory()->create();
        $sentAt = now()->subHours(1);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'template' => Template::CUSTOMER_NOTICE_TEMPLATE->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::AFFECTED_DATA_SUBJECTS->value,
            'channel' => Channel::EMAIL->value,
            'regulatory_basis' => RegulatoryBasis::GDPR_ART_34->value,
            'notice_summary' => 'We are investigating an issue affecting AI model responses. Our team is working to resolve this as quickly as possible.',
            'notice_link' => 'https://status.example.com/incidents/2024-10-29-ai-response-delay',
            'sent_at' => $sentAt->toDateTimeString(),
            'sent_by' => 'notifications@company.com',
            'delivery_status' => DeliveryStatus::DELIVERED->value,
            'response_summary' => '50,000 customers notified via email',
            'follow_up_required' => true,
            'follow_up_date' => now()->addDays(7)->toDateTimeString(),
            'follow_up_notes' => 'Send resolution update to affected customers',
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals(AudienceType::AFFECTED_DATA_SUBJECTS->value, $notification->audience_type);
        $this->assertEquals(Channel::EMAIL->value, $notification->channel);
        $this->assertNotNull($notification->notice_link);
        $this->assertEquals('50,000 customers notified via email', $notification->response_summary);
        $this->assertTrue($notification->follow_up_required);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => AudienceType::AFFECTED_DATA_SUBJECTS->value,
            'sent_by' => 'notifications@company.com',
        ]);
    }

    public function test_create_complete_internal_notification_scenario(): void
    {
        $incident = AiIncident::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'ai_incident_id' => $incident->id,
            'template' => Template::INTERNAL_ALL_HANDS_TEMPLATE->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_EXECUTIVE->value,
            'channel' => Channel::SLACK_TEAMS->value,
            'notice_summary' => 'Executive briefing: AI model incident and remediation steps.',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DELIVERED->value,
            'follow_up_required' => false,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals(AudienceType::INTERNAL_EXECUTIVE->value, $notification->audience_type);
        $this->assertEquals(Channel::SLACK_TEAMS->value, $notification->channel);
        $this->assertEquals(Template::INTERNAL_ALL_HANDS_TEMPLATE->value, $notification->template);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => AudienceType::INTERNAL_EXECUTIVE->value,
        ]);
    }
}
