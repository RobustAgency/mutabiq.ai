<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\IncidentNotification;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IncidentNotificationObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_incident_notification_create(): void
    {
        $notification = IncidentNotification::factory()->create();

        $log = ActivityLog::where('actable_id', $notification->id)
            ->where('actable_type', IncidentNotification::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('IncidentNotification created', $log->description);
    }

    public function test_logs_activity_on_incident_notification_update(): void
    {
        $notification = IncidentNotification::factory()->create(['delivery_status' => 'PENDING']);

        ActivityLog::truncate();

        $notification->update(['delivery_status' => 'DELIVERED']);

        $log = ActivityLog::where('actable_id', $notification->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('IncidentNotification updated', $log->description);
        $this->assertArrayHasKey('delivery_status', $log->changes);
        $this->assertEquals('PENDING', $log->changes['delivery_status']['from']);
        $this->assertEquals('DELIVERED', $log->changes['delivery_status']['to']);
    }

    public function test_logs_activity_on_incident_notification_delete(): void
    {
        $notification = IncidentNotification::factory()->create();
        $notificationId = $notification->id;

        $notification->delete();

        $log = ActivityLog::where('actable_id', $notificationId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('IncidentNotification deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $notification = IncidentNotification::factory()->create([
            'template' => 'GDPR_BREACH',
            'language' => 'EN',
            'audience_type' => 'DATA_SUBJECTS',
            'channel' => 'EMAIL',
            'delivery_status' => 'PENDING',
        ]);

        ActivityLog::truncate();

        $notification->update([
            'template' => 'CCPA_BREACH',
            'language' => 'ES',
            'audience_type' => 'REGULATORS',
            'channel' => 'REGISTERED_MAIL',
            'delivery_status' => 'DELIVERED',
        ]);

        $log = ActivityLog::where('actable_id', $notification->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('template', $log->changes);
        $this->assertArrayHasKey('language', $log->changes);
        $this->assertArrayHasKey('audience_type', $log->changes);
        $this->assertArrayHasKey('channel', $log->changes);
        $this->assertArrayHasKey('delivery_status', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $notification = IncidentNotification::factory()->create();

        $log = ActivityLog::where('actable_id', $notification->id)
            ->where('actable_type', IncidentNotification::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($notification->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $notification = IncidentNotification::factory()->create();

        $log = ActivityLog::where('actable_id', $notification->id)
            ->where('actable_type', IncidentNotification::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_notification_deadline_and_sent_at(): void
    {
        $notification = IncidentNotification::factory()->create([
            'notification_deadline' => now()->addDays(30),
            'sent_at' => now()->addDays(10),
            'sent_by' => null,
        ]);

        ActivityLog::truncate();

        $notification->update([
            'sent_at' => now(),
            'sent_by' => 'compliance@example.com',
        ]);

        $log = ActivityLog::where('actable_id', $notification->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('sent_at', $log->changes);
        $this->assertArrayHasKey('sent_by', $log->changes);
    }

    public function test_tracks_follow_up_changes(): void
    {
        $notification = IncidentNotification::factory()->create([
            'follow_up_required' => false,
            'follow_up_date' => null,
            'follow_up_notes' => null,
        ]);

        ActivityLog::truncate();

        $notification->update([
            'follow_up_required' => true,
            'follow_up_date' => now()->addDays(7),
            'follow_up_notes' => 'Pending response from data subjects',
        ]);

        $log = ActivityLog::where('actable_id', $notification->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('follow_up_required', $log->changes);
        $this->assertArrayHasKey('follow_up_date', $log->changes);
        $this->assertArrayHasKey('follow_up_notes', $log->changes);
    }

    public function test_tracks_regulatory_basis_changes(): void
    {
        $notification = IncidentNotification::factory()->create([
            'regulatory_basis' => 'GDPR_ARTICLE_33',
        ]);

        ActivityLog::truncate();

        $notification->update([
            'regulatory_basis' => 'GDPR_ARTICLE_34',
        ]);

        $log = ActivityLog::where('actable_id', $notification->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('regulatory_basis', $log->changes);
    }
}
