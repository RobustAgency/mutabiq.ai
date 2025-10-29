<?php

namespace Tests\Feature;

use App\Models\AiIncident;
use App\Models\IncidentNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
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
                            'audience_type',
                            'channel',
                            'notice_summary',
                            'notice_link',
                            'notified_at',
                            'approved_by',
                            'approval_ref',
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
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Internal notification about incident response',
            'notified_at' => now()->toDateTimeString(),
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
            ->assertJsonPath('data.audience_type', 'internal_staff')
            ->assertJsonPath('data.channel', 'email')
            ->assertJsonPath('data.follow_up_required', false);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_staff',
            'channel' => 'email',
        ]);
    }

    public function test_store_creates_incident_notification_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $notifiedAt = now()->subHours(2);

        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'customers',
            'channel' => 'status_page',
            'notice_summary' => 'We are experiencing technical difficulties with our AI service',
            'notice_link' => 'https://example.com/status/incident-123',
            'notified_at' => $notifiedAt->toDateTimeString(),
            'approved_by' => 'Jane Smith',
            'approval_ref' => 'APPR-1234',
            'follow_up_required' => true,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.audience_type', 'customers')
            ->assertJsonPath('data.channel', 'status_page')
            ->assertJsonPath('data.notice_link', 'https://example.com/status/incident-123')
            ->assertJsonPath('data.approved_by', 'Jane Smith')
            ->assertJsonPath('data.approval_ref', 'APPR-1234')
            ->assertJsonPath('data.follow_up_required', true);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'customers',
            'approved_by' => 'Jane Smith',
        ]);
    }

    public function test_store_validates_ai_incident_id_is_required(): void
    {
        $data = [
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Test notification',
            'notified_at' => now()->toDateTimeString(),
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
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Test notification',
            'notified_at' => now()->toDateTimeString(),
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
            'channel' => 'email',
            'notice_summary' => 'Test notification',
            'notified_at' => now()->toDateTimeString(),
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
            'audience_type' => 'invalid_audience',
            'channel' => 'email',
            'notice_summary' => 'Test notification',
            'notified_at' => now()->toDateTimeString(),
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
            'audience_type' => 'internal_staff',
            'notice_summary' => 'Test notification',
            'notified_at' => now()->toDateTimeString(),
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
            'audience_type' => 'internal_staff',
            'channel' => 'invalid_channel',
            'notice_summary' => 'Test notification',
            'notified_at' => now()->toDateTimeString(),
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
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notified_at' => now()->toDateTimeString(),
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notice_summary']);
    }

    public function test_store_validates_notified_at_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Test notification',
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notified_at']);
    }

    public function test_store_validates_follow_up_required_is_required(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Test notification',
            'notified_at' => now()->toDateTimeString(),
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
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Test notification',
            'notified_at' => now()->toDateTimeString(),
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
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Test notification',
            'notified_at' => now()->toDateTimeString(),
            'follow_up_required' => false,
            'notice_link' => 'not-a-valid-url',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notice_link']);
    }

    public function test_store_does_not_require_approved_by_for_internal_exec(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_exec',
            'channel' => 'email',
            'notice_summary' => 'Executive notification',
            'notified_at' => now()->toDateTimeString(),
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.audience_type', 'internal_exec');
    }

    public function test_store_does_not_require_approved_by_for_internal_staff(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_staff',
            'channel' => 'portal',
            'notice_summary' => 'Staff notification',
            'notified_at' => now()->toDateTimeString(),
            'follow_up_required' => false,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/incident-notifications', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.audience_type', 'internal_staff');
    }

    public function test_store_accepts_all_valid_audience_types(): void
    {
        $incident = AiIncident::factory()->create();
        $audienceTypes = [
            'internal_exec' => false,
            'internal_staff' => false,
            'customers' => true,
            'regulator' => true,
            'vendor' => true,
            'media' => true,
            'other' => false,
        ];

        foreach ($audienceTypes as $audienceType => $requiresApproval) {
            $data = [
                'ai_incident_id' => $incident->id,
                'audience_type' => $audienceType,
                'channel' => 'email',
                'notice_summary' => "Test notification for {$audienceType}",
                'notified_at' => now()->toDateTimeString(),
                'follow_up_required' => false,
            ];

            if ($requiresApproval) {
                $data['approved_by'] = 'Approver Name';
            }

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/incident-notifications', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.audience_type', $audienceType);
        }
    }

    public function test_store_accepts_all_valid_channels(): void
    {
        $incident = AiIncident::factory()->create();
        $channels = ['email', 'portal', 'status_page', 'phone', 'meeting', 'legal_letter', 'other'];

        foreach ($channels as $channel) {
            $data = [
                'ai_incident_id' => $incident->id,
                'audience_type' => 'internal_staff',
                'channel' => $channel,
                'notice_summary' => "Test notification via {$channel}",
                'notified_at' => now()->toDateTimeString(),
                'follow_up_required' => false,
            ];

            $response = $this->actingAs($this->user, 'supabase')
                ->postJson('/api/incident-notifications', $data);

            $response->assertStatus(201)
                ->assertJsonPath('data.channel', $channel);
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
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'follow_up_required' => false,
        ]);

        $updateData = [
            'audience_type' => 'internal_exec',
            'channel' => 'meeting',
            'follow_up_required' => true,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-notifications/{$notification->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Incident notification updated successfully',
            ])
            ->assertJsonPath('data.audience_type', 'internal_exec')
            ->assertJsonPath('data.channel', 'meeting')
            ->assertJsonPath('data.follow_up_required', true);

        $this->assertDatabaseHas('incident_notifications', [
            'id' => $notification->id,
            'audience_type' => 'internal_exec',
            'channel' => 'meeting',
        ]);
    }

    public function test_update_partially_updates_incident_notification(): void
    {
        $notification = IncidentNotification::factory()->create([
            'audience_type' => 'internal_staff',
            'approval_ref' => null,
        ]);

        $updateData = [
            'approval_ref' => 'APPR-9999',
            'notice_link' => 'https://example.com/updated-link',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/incident-notifications/{$notification->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.audience_type', 'internal_staff')
            ->assertJsonPath('data.approval_ref', 'APPR-9999')
            ->assertJsonPath('data.notice_link', 'https://example.com/updated-link');

        $this->assertDatabaseHas('incident_notifications', [
            'id' => $notification->id,
            'audience_type' => 'internal_staff',
        ]);
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
