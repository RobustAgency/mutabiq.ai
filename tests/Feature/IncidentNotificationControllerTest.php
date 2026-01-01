<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
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

class IncidentNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
    }

    public function test_index_returns_paginated_incident_notifications(): void
    {
        IncidentNotification::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/incident-notifications');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident notifications retrieved successfully',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'ai_incident_id',
                            'template',
                            'language',
                            'audience_type',
                            'channel',
                            'regulatory_basis',
                            'notice_summary',
                            'notice_link',
                            'sent_at',
                            'delivery_status',
                            'follow_up_required',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_store_creates_incident_notification_with_required_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'regulatory_basis' => RegulatoryBasis::GDPR_ART_33->value,
            'notice_summary' => 'Internal notification about incident response',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Incident notification created successfully',
            ])
            ->assertJsonPath('data.ai_incident_id', $incident->id)
            ->assertJsonPath('data.audience_type', AudienceType::INTERNAL_TECHNICAL->value)
            ->assertJsonPath('data.channel', Channel::EMAIL->value)
            ->assertJsonPath('data.follow_up_required', false);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
        ]);
    }

    public function test_store_creates_incident_notification_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $sentAt = now()->subHours(2);
        $notificationDeadline = now()->addDays(7);
        $followUpDate = now()->addDays(14);

        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::CUSTOMER_NOTICE_TEMPLATE->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::AFFECTED_DATA_SUBJECTS->value,
            'channel' => Channel::EMAIL->value,
            'regulatory_basis' => RegulatoryBasis::GDPR_ART_34->value,
            'notification_deadline' => $notificationDeadline->toDateTimeString(),
            'notice_summary' => 'We are experiencing technical difficulties with our AI service',
            'notice_link' => 'https://example.com/status/incident-123',
            'sent_at' => $sentAt->toDateTimeString(),
            'sent_by' => 'notifications@example.com',
            'delivery_status' => DeliveryStatus::DELIVERED->value,
            'response_summary' => 'All customers notified successfully',
            'follow_up_required' => true,
            'follow_up_date' => $followUpDate->toDateTimeString(),
            'follow_up_notes' => 'Monitor customer response and complaints',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.audience_type', AudienceType::AFFECTED_DATA_SUBJECTS->value)
            ->assertJsonPath('data.channel', Channel::EMAIL->value)
            ->assertJsonPath('data.notice_link', 'https://example.com/status/incident-123')
            ->assertJsonPath('data.sent_by', 'notifications@example.com')
            ->assertJsonPath('data.follow_up_required', true);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => AudienceType::AFFECTED_DATA_SUBJECTS->value,
            'sent_by' => 'notifications@example.com',
        ]);
    }

    public function test_store_validates_ai_incident_id_is_required(): void
    {
        $data = [
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_incident_id']);
    }

    public function test_store_validates_ai_incident_id_exists(): void
    {
        $data = [
            'ai_incident_id' => 99999,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_incident_id']);
    }

    public function test_store_validates_audience_type_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['audience_type']);
    }

    public function test_store_validates_audience_type_enum(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => 'invalid_audience',
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['audience_type']);
    }

    public function test_store_validates_channel_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['channel']);
    }

    public function test_store_validates_channel_enum(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => 'invalid_channel',
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['channel']);
    }

    public function test_store_validates_notice_summary_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notice_summary']);
    }

    public function test_store_validates_sent_at_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Test notification',
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sent_at']);
    }

    public function test_store_validates_delivery_status_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_status']);
    }

    public function test_store_validates_follow_up_required_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['follow_up_required']);
    }

    public function test_store_validates_follow_up_required_is_boolean(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => 'not_a_boolean',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['follow_up_required']);
    }

    public function test_store_validates_notice_link_is_url(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'template' => Template::DPA_BREACH_NOTIFICATION->value,
            'language' => Language::ENGLISH->value,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'channel' => Channel::EMAIL->value,
            'notice_summary' => 'Test notification',
            'sent_at' => now()->toDateTimeString(),
            'delivery_status' => DeliveryStatus::DRAFT->value,
            'follow_up_required' => false,
            'notice_link' => 'not-a-valid-url',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notice_link']);
    }

    public function test_store_accepts_all_valid_audience_types(): void
    {
        $incident = AiIncident::factory()->create();

        foreach (AudienceType::cases() as $audienceType) {
            $data = [
                'ai_incident_id' => $incident->id,
                'template' => Template::CUSTOM_OTHER->value,
                'language' => Language::ENGLISH->value,
                'audience_type' => $audienceType->value,
                'channel' => Channel::EMAIL->value,
                'notice_summary' => "Test notification for {$audienceType->value}",
                'sent_at' => now()->toDateTimeString(),
                'delivery_status' => DeliveryStatus::SENT->value,
                'follow_up_required' => false,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/incident-notifications', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.audience_type', $audienceType->value);
        }
    }

    public function test_store_accepts_all_valid_channels(): void
    {
        $incident = AiIncident::factory()->create();

        foreach (Channel::cases() as $channel) {
            $data = [
                'ai_incident_id' => $incident->id,
                'template' => Template::CUSTOM_OTHER->value,
                'language' => Language::ENGLISH->value,
                'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
                'channel' => $channel->value,
                'notice_summary' => "Test notification via {$channel->value}",
                'sent_at' => now()->toDateTimeString(),
                'delivery_status' => DeliveryStatus::SENT->value,
                'follow_up_required' => false,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/incident-notifications', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.channel', $channel->value);
        }
    }

    public function test_store_accepts_all_delivery_statuses(): void
    {
        $incident = AiIncident::factory()->create();

        foreach (DeliveryStatus::cases() as $status) {
            $data = [
                'ai_incident_id' => $incident->id,
                'template' => Template::CUSTOM_OTHER->value,
                'language' => Language::ENGLISH->value,
                'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
                'channel' => Channel::EMAIL->value,
                'notice_summary' => "Test notification with status {$status->value}",
                'sent_at' => now()->toDateTimeString(),
                'delivery_status' => $status->value,
                'follow_up_required' => false,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/incident-notifications', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.delivery_status', $status->value);
        }
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/incident-notifications', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_incident_notification(): void
    {
        $notification = IncidentNotification::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/incident-notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident notification retrieved successfully',
            ])
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.audience_type', $notification->audience_type);
    }

    public function test_show_returns_404_for_non_existent_notification(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/incident-notifications/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $notification = IncidentNotification::factory()->create();

        $response = $this->getJson("/api/incident-notifications/{$notification->id}");

        $response->assertStatus(401);
    }

    public function test_update_updates_incident_notification(): void
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
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-notifications/{$notification->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident notification updated successfully',
            ])
            ->assertJsonPath('data.audience_type', AudienceType::INTERNAL_EXECUTIVE->value)
            ->assertJsonPath('data.channel', Channel::SLACK_TEAMS->value)
            ->assertJsonPath('data.follow_up_required', true);

        $this->assertDatabaseHas('incident_notifications', [
            'id' => $notification->id,
            'audience_type' => AudienceType::INTERNAL_EXECUTIVE->value,
            'channel' => Channel::SLACK_TEAMS->value,
        ]);
    }

    public function test_update_partially_updates_incident_notification(): void
    {
        $notification = IncidentNotification::factory()->create([
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
            'sent_by' => null,
        ]);

        $updateData = [
            'sent_by' => 'dpo@example.com',
            'notice_link' => 'https://example.com/updated-link',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-notifications/{$notification->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.audience_type', AudienceType::INTERNAL_TECHNICAL->value)
            ->assertJsonPath('data.sent_by', 'dpo@example.com')
            ->assertJsonPath('data.notice_link', 'https://example.com/updated-link');

        $this->assertDatabaseHas('incident_notifications', [
            'id' => $notification->id,
            'audience_type' => AudienceType::INTERNAL_TECHNICAL->value,
        ]);
    }

    public function test_update_updates_delivery_status(): void
    {
        $notification = IncidentNotification::factory()->create([
            'delivery_status' => DeliveryStatus::SENT->value,
        ]);

        $updateData = [
            'delivery_status' => DeliveryStatus::DELIVERED->value,
            'response_summary' => 'Successfully delivered to all recipients',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-notifications/{$notification->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.delivery_status', DeliveryStatus::DELIVERED->value)
            ->assertJsonPath('data.response_summary', 'Successfully delivered to all recipients');
    }

    public function test_update_requires_authentication(): void
    {
        $notification = IncidentNotification::factory()->create();

        $response = $this->postJson("/api/incident-notifications/{$notification->id}", []);

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_incident_notification(): void
    {
        $notification = IncidentNotification::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/incident-notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident notification deleted successfully',
            ]);

        $this->assertDatabaseMissing('incident_notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_destroy_returns_404_for_non_existent_notification(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/incident-notifications/99999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $notification = IncidentNotification::factory()->create();

        $response = $this->deleteJson("/api/incident-notifications/{$notification->id}");

        $response->assertStatus(401);
    }
}
