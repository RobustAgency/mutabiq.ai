<?php

namespace Tests\Feature\Mail;

use Tests\TestCase;
use App\Models\Role;
use App\Models\TeamInvitation;
use App\Mail\TeamInvitationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamInvitationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_loads_role_properly(): void
    {
        $role = Role::factory()->create(['name' => 'Admin']);
        $invitation = TeamInvitation::factory()->create(['role_id' => $role->id]);

        $mail = new TeamInvitationMail($invitation);
        $content = $mail->content();

        // Verify the role is loaded
        $this->assertTrue($invitation->relationLoaded('role'));
        $this->assertNotNull($invitation->role);
        $this->assertEquals($role->id, $invitation->role->id);
        $this->assertEquals('Admin', $invitation->role->name);
    }

    public function test_mail_contains_correct_data(): void
    {
        $role = Role::factory()->create(['name' => 'Manager']);
        $invitation = TeamInvitation::factory()->create([
            'role_id' => $role->id,
            'token' => 'test-token-123',
        ]);

        $mail = new TeamInvitationMail($invitation);
        $content = $mail->content();

        $expectedUrl = config('app.frontend_url').'/accept-invite?token=test-token-123';

        // Verify the mail has correct markdown view and data
        $this->assertEquals('emails.team_invitation', $content->markdown);
        $this->assertArrayHasKey('invitation', $content->with);
        $this->assertArrayHasKey('signupUrl', $content->with);
        $this->assertEquals($expectedUrl, $content->with['signupUrl']);
    }

    public function test_mail_envelope_has_subject(): void
    {
        $invitation = TeamInvitation::factory()->create();
        $mail = new TeamInvitationMail($invitation);
        $envelope = $mail->envelope();

        $this->assertEquals('Team Invitation Mail', $envelope->subject);
    }

    public function test_mail_has_no_attachments(): void
    {
        $invitation = TeamInvitation::factory()->create();
        $mail = new TeamInvitationMail($invitation);

        $this->assertEmpty($mail->attachments());
    }

    public function test_mail_implements_should_queue(): void
    {
        $invitation = TeamInvitation::factory()->create();
        $mail = new TeamInvitationMail($invitation);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $mail);
    }
}
