<?php

namespace Tests\Feature\Repositories;

use App\Models\AiIncident;
use App\Models\IncidentNotification;
use App\Repositories\IncidentNotificationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentNotificationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected IncidentNotificationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new IncidentNotificationRepository();
    }

    public function test_paginate_returns_paginated_incident_notifications(): void
    {
        IncidentNotification::factory()->count(15)->create();

        $result = $this->repository->getPaginatedIncidentNotifications(10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_paginate_with_default_per_page(): void
    {
        IncidentNotification::factory()->count(5)->create();

        $result = $this->repository->getPaginatedIncidentNotifications();

        $this->assertCount(5, $result->items());
    }

    public function test_create_stores_incident_notification_with_required_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Test notification summary',
            'notified_at' => now()->toDateTimeString(),
            'follow_up_required' => false,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertInstanceOf(IncidentNotification::class, $notification);
        $this->assertEquals($incident->id, $notification->ai_incident_id);
        $this->assertEquals('internal_staff', $notification->audience_type);
        $this->assertEquals('email', $notification->channel);
        $this->assertEquals('Test notification summary', $notification->notice_summary);
        $this->assertFalse($notification->follow_up_required);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_staff',
            'channel' => 'email',
        ]);
    }

    public function test_create_stores_incident_notification_with_all_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $notifiedAt = now()->subHours(3);

        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'customers',
            'channel' => 'status_page',
            'notice_summary' => 'Customer-facing incident notification',
            'notice_link' => 'https://status.example.com/incident-456',
            'notified_at' => $notifiedAt->toDateTimeString(),
            'approved_by' => 'John Doe',
            'approval_ref' => 'APPR-2024-456',
            'follow_up_required' => true,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals('customers', $notification->audience_type);
        $this->assertEquals('status_page', $notification->channel);
        $this->assertEquals('https://status.example.com/incident-456', $notification->notice_link);
        $this->assertEquals('John Doe', $notification->approved_by);
        $this->assertEquals('APPR-2024-456', $notification->approval_ref);
        $this->assertTrue($notification->follow_up_required);
    }

    public function test_create_handles_internal_exec_audience(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_exec',
            'channel' => 'meeting',
            'notice_summary' => 'Executive briefing on incident',
            'notified_at' => now()->toDateTimeString(),
            'follow_up_required' => true,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals('internal_exec', $notification->audience_type);
        $this->assertEquals('meeting', $notification->channel);
        $this->assertNull($notification->approved_by);
    }

    public function test_create_handles_regulator_audience_with_approval(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'regulator',
            'channel' => 'legal_letter',
            'notice_summary' => 'Formal notification to regulatory authority',
            'notified_at' => now()->toDateTimeString(),
            'approved_by' => 'Legal Department Head',
            'approval_ref' => 'LEGAL-2024-789',
            'follow_up_required' => true,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals('regulator', $notification->audience_type);
        $this->assertEquals('legal_letter', $notification->channel);
        $this->assertEquals('Legal Department Head', $notification->approved_by);
    }

    public function test_create_handles_vendor_audience(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'vendor',
            'channel' => 'email',
            'notice_summary' => 'Vendor notification about service impact',
            'notified_at' => now()->toDateTimeString(),
            'approved_by' => 'Vendor Relations Manager',
            'follow_up_required' => false,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals('vendor', $notification->audience_type);
        $this->assertEquals('Vendor Relations Manager', $notification->approved_by);
    }

    public function test_create_handles_media_audience(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'media',
            'channel' => 'email',
            'notice_summary' => 'Press release about incident',
            'notified_at' => now()->toDateTimeString(),
            'approved_by' => 'PR Director',
            'approval_ref' => 'PR-2024-123',
            'follow_up_required' => true,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals('media', $notification->audience_type);
        $this->assertEquals('PR Director', $notification->approved_by);
    }

    public function test_create_handles_all_channel_types(): void
    {
        $incident = AiIncident::factory()->create();
        $channels = ['email', 'portal', 'status_page', 'phone', 'meeting', 'legal_letter', 'other'];

        foreach ($channels as $channel) {
            $data = [
                'ai_incident_id' => $incident->id,
                'audience_type' => 'internal_staff',
                'channel' => $channel,
                'notice_summary' => "Notification via {$channel}",
                'notified_at' => now()->toDateTimeString(),
                'follow_up_required' => false,
            ];

            $notification = $this->repository->createIncidentNotification($data);
            $this->assertEquals($channel, $notification->channel);
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
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'follow_up_required' => false,
        ]);

        $updateData = [
            'audience_type' => 'internal_exec',
            'channel' => 'meeting',
            'follow_up_required' => true,
            'notice_link' => 'https://example.com/meeting-notes',
        ];

        $updated = $this->repository->updateIncidentNotification($notification, $updateData);

        $this->assertEquals('internal_exec', $updated->audience_type);
        $this->assertEquals('meeting', $updated->channel);
        $this->assertTrue($updated->follow_up_required);
        $this->assertEquals('https://example.com/meeting-notes', $updated->notice_link);
    }

    public function test_update_modifies_only_provided_fields(): void
    {
        $notification = IncidentNotification::factory()->create([
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Original summary',
        ]);

        $updateData = [
            'notice_summary' => 'Updated summary',
        ];

        $updated = $this->repository->updateIncidentNotification($notification, $updateData);

        $this->assertEquals('internal_staff', $updated->audience_type);
        $this->assertEquals('email', $updated->channel);
        $this->assertEquals('Updated summary', $updated->notice_summary);
    }

    public function test_update_can_add_approval_information(): void
    {
        $notification = IncidentNotification::factory()->create([
            'audience_type' => 'internal_staff',
            'approved_by' => null,
            'approval_ref' => null,
        ]);

        $updateData = [
            'approved_by' => 'Manager Name',
            'approval_ref' => 'APPR-2024-999',
        ];

        $updated = $this->repository->updateIncidentNotification($notification, $updateData);

        $this->assertEquals('Manager Name', $updated->approved_by);
        $this->assertEquals('APPR-2024-999', $updated->approval_ref);
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

        $result = $this->repository->getPaginatedIncidentNotifications();

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

    public function test_repository_handles_long_notice_summary(): void
    {
        $incident = AiIncident::factory()->create();
        $longSummary = str_repeat('This is a very detailed notification summary. ', 50);

        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'customers',
            'channel' => 'status_page',
            'notice_summary' => $longSummary,
            'notified_at' => now()->toDateTimeString(),
            'approved_by' => 'Approver',
            'follow_up_required' => false,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals($longSummary, $notification->notice_summary);
    }

    public function test_repository_handles_notification_with_null_optional_fields(): void
    {
        $incident = AiIncident::factory()->create();
        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Basic notification',
            'notice_link' => null,
            'notified_at' => now()->toDateTimeString(),
            'approved_by' => null,
            'approval_ref' => null,
            'follow_up_required' => false,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertNull($notification->notice_link);
        $this->assertNull($notification->approved_by);
        $this->assertNull($notification->approval_ref);
    }

    public function test_repository_handles_notified_at_datetime_properly(): void
    {
        $incident = AiIncident::factory()->create();
        $specificDateTime = now()->subDays(2)->setTime(14, 30, 0);

        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'internal_staff',
            'channel' => 'email',
            'notice_summary' => 'Delayed notification',
            'notified_at' => $specificDateTime->toDateTimeString(),
            'follow_up_required' => false,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals($specificDateTime->format('Y-m-d H:i:s'), $notification->notified_at->format('Y-m-d H:i:s'));
    }

    public function test_create_complete_customer_notification_scenario(): void
    {
        $incident = AiIncident::factory()->create();
        $notifiedAt = now()->subHours(1);

        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'customers',
            'channel' => 'status_page',
            'notice_summary' => 'We are investigating an issue affecting AI model responses. Our team is working to resolve this as quickly as possible.',
            'notice_link' => 'https://status.example.com/incidents/2024-10-29-ai-response-delay',
            'notified_at' => $notifiedAt->toDateTimeString(),
            'approved_by' => 'Sarah Johnson, VP Customer Success',
            'approval_ref' => 'CS-APPR-2024-1029',
            'follow_up_required' => true,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals('customers', $notification->audience_type);
        $this->assertEquals('status_page', $notification->channel);
        $this->assertNotNull($notification->notice_link);
        $this->assertEquals('Sarah Johnson, VP Customer Success', $notification->approved_by);
        $this->assertEquals('CS-APPR-2024-1029', $notification->approval_ref);
        $this->assertTrue($notification->follow_up_required);

        $this->assertDatabaseHas('incident_notifications', [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'customers',
            'approved_by' => 'Sarah Johnson, VP Customer Success',
        ]);
    }

    public function test_create_complete_regulator_notification_scenario(): void
    {
        $incident = AiIncident::factory()->create();

        $data = [
            'ai_incident_id' => $incident->id,
            'audience_type' => 'regulator',
            'channel' => 'legal_letter',
            'notice_summary' => 'Formal notification of AI system incident per regulatory requirements under Section 42.1 of AI Governance Act.',
            'notice_link' => 'https://compliance.example.com/regulatory-submissions/INC-2024-1029',
            'notified_at' => now()->toDateTimeString(),
            'approved_by' => 'Michael Chen, Chief Compliance Officer',
            'approval_ref' => 'CCO-REG-2024-1029-FORMAL',
            'follow_up_required' => true,
        ];

        $notification = $this->repository->createIncidentNotification($data);

        $this->assertEquals('regulator', $notification->audience_type);
        $this->assertEquals('legal_letter', $notification->channel);
        $this->assertStringContainsString('regulatory requirements', $notification->notice_summary);
        $this->assertEquals('Michael Chen, Chief Compliance Officer', $notification->approved_by);
        $this->assertTrue($notification->follow_up_required);
    }
}
