<?php

namespace Tests\Feature\Repositories;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Organization;
use App\Models\TeamInvitation;
use App\Enums\InvitationStatus;
use Illuminate\Foundation\Testing\WithFaker;
use App\Repositories\TeamInvitationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamInvitationRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private TeamInvitationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new TeamInvitationRepository;
    }

    public function test_get_by_token_returns_invitation_with_role(): void
    {
        $invitation = TeamInvitation::factory()->create();

        $result = $this->repository->getByToken($invitation->token);

        $this->assertNotNull($result);
        $this->assertEquals($invitation->id, $result->id);
        $this->assertEquals($invitation->token, $result->token);
        $this->assertTrue($result->relationLoaded('role'));
    }

    public function test_get_by_token_returns_null_when_token_not_found(): void
    {
        $result = $this->repository->getByToken('non-existent-token');

        $this->assertNull($result);
    }

    public function test_create_invite_creates_invitation_with_pending_status(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $role = Role::factory()->create();

        $members = [
            'email' => $this->faker->safeEmail(),
            'role_id' => $role->id,
        ];

        Carbon::setTestNow(now());
        $invitation = $this->repository->createInvite($user, $members);

        $this->assertNotNull($invitation->id);
        $this->assertEquals($organization->id, $invitation->organization_id);
        $this->assertEquals($user->id, $invitation->invited_by);
        $this->assertEquals($members['email'], $invitation->email);
        $this->assertEquals($role->id, $invitation->role_id);
        $this->assertEquals(InvitationStatus::PENDING, $invitation->status);
        $this->assertNotNull($invitation->token);
        $this->assertTrue($invitation->expires_at->isSameDay(now()->addDays(7)));

        $this->assertDatabaseHas('team_invitations', [
            'email' => $members['email'],
            'organization_id' => $organization->id,
            'status' => InvitationStatus::PENDING->value,
        ]);
    }

    public function test_create_invite_generates_unique_token(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $role = Role::factory()->create();

        $members1 = [
            'email' => 'user1@example.com',
            'role_id' => $role->id,
        ];

        $members2 = [
            'email' => 'user2@example.com',
            'role_id' => $role->id,
        ];

        $invitation1 = $this->repository->createInvite($user, $members1);
        $invitation2 = $this->repository->createInvite($user, $members2);

        $this->assertNotEquals($invitation1->token, $invitation2->token);
    }

    public function test_mark_as_accepted_updates_status(): void
    {
        $invitation = TeamInvitation::factory()->create(['status' => InvitationStatus::PENDING->value]);

        $result = $this->repository->markAsAccepted($invitation);

        $this->assertEquals(InvitationStatus::ACCEPTED, $result->status);
        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'status' => InvitationStatus::ACCEPTED->value,
        ]);
    }

    public function test_handles_all_invitation_statuses(): void
    {
        foreach (InvitationStatus::cases() as $status) {
            $invitation = TeamInvitation::factory()->create(['status' => $status->value]);

            $this->assertEquals($status, $invitation->status);
        }
    }
}
