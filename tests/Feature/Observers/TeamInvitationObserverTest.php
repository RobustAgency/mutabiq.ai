<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\TeamInvitation;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamInvitationObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_team_invitation_create(): void
    {
        $invitation = TeamInvitation::factory()->create();

        $log = ActivityLog::where('actable_id', $invitation->id)
            ->where('actable_type', TeamInvitation::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('TeamInvitation created', $log->description);
    }

    public function test_logs_activity_on_team_invitation_update(): void
    {
        $invitation = TeamInvitation::factory()->create(['status' => 'pending']);

        ActivityLog::truncate();

        $invitation->update(['status' => 'accepted']);

        $log = ActivityLog::where('actable_id', $invitation->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('TeamInvitation updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('pending', $log->changes['status']['from']);
        $this->assertEquals('accepted', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_team_invitation_delete(): void
    {
        $invitation = TeamInvitation::factory()->create();
        $invitationId = $invitation->id;

        $invitation->delete();

        $log = ActivityLog::where('actable_id', $invitationId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('TeamInvitation deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'email' => 'original@example.com',
            'role' => 'auditor',
            'status' => 'pending',
        ]);

        ActivityLog::truncate();

        $invitation->update([
            'email' => 'updated@example.com',
            'role' => 'reviewer',
            'status' => 'accepted',
        ]);

        $log = ActivityLog::where('actable_id', $invitation->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('email', $log->changes);
        $this->assertArrayHasKey('role', $log->changes);
        $this->assertArrayHasKey('status', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $invitation = TeamInvitation::factory()->create();

        $log = ActivityLog::where('actable_id', $invitation->id)
            ->where('actable_type', TeamInvitation::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($invitation->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $invitation = TeamInvitation::factory()->create();

        $log = ActivityLog::where('actable_id', $invitation->id)
            ->where('actable_type', TeamInvitation::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_expiration_changes(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'expires_at' => now()->addDays(7),
        ]);

        ActivityLog::truncate();

        $invitation->update([
            'expires_at' => now()->addDays(14),
        ]);

        $log = ActivityLog::where('actable_id', $invitation->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('expires_at', $log->changes);
    }
}
