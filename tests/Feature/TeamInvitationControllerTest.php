<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use App\Models\Organization;
use App\Models\TeamInvitation;
use App\Clients\SupabaseClient;
use App\Enums\InvitationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamInvitationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        setPermissionsTeamId($this->organization->id);

        $permissions = [
            'administration.users.create',
            'administration.users.edit',
            'administration.users.view',
            'administration.users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'supabase',
            ]);
        }

        $role = Role::factory()->create([
            'name' => 'User Manager',
            'guard_name' => 'supabase',
        ]);

        foreach ($permissions as $permission) {
            $role->givePermissionTo($permission);
        }

        $this->user->assignRole($role);
    }

    public function test_invite_members_sends_invitations_successfully(): void
    {
        $role = Role::factory()->create();

        $members = [
            [
                'email' => 'user1@example.com',
                'role_id' => $role->id,
            ],
            [
                'email' => 'user2@example.com',
                'role_id' => $role->id,
            ],
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/invite-members', [
                'members' => $members,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Invitations sent successfully',
                'data' => null,
            ]);

        $this->assertDatabaseHas('team_invitations', [
            'email' => 'user1@example.com',
            'organization_id' => $this->organization->id,
            'status' => InvitationStatus::PENDING->value,
        ]);

        $this->assertDatabaseHas('team_invitations', [
            'email' => 'user2@example.com',
            'organization_id' => $this->organization->id,
            'status' => InvitationStatus::PENDING->value,
        ]);
    }

    public function test_invite_members_creates_invitation_with_correct_data(): void
    {
        $role = Role::factory()->create();

        $members = [
            [
                'email' => 'newuser@example.com',
                'role_id' => $role->id,
            ],
        ];

        $this->actingAs($this->user, 'supabase')
            ->postJson('/api/invite-members', [
                'members' => $members,
            ]);

        $invitation = TeamInvitation::where('email', 'newuser@example.com')->first();

        $this->assertNotNull($invitation);
        $this->assertEquals($this->user->id, $invitation->invited_by);
        $this->assertEquals($this->organization->id, $invitation->organization_id);
        $this->assertEquals($role->id, $invitation->role_id);
        $this->assertEquals(InvitationStatus::PENDING, $invitation->status);
        $this->assertNotNull($invitation->token);
    }

    public function test_accept_invitation_with_valid_token(): void
    {
        $role = Role::factory()->create(['name' => 'admin']);
        $invitation = TeamInvitation::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $role->id,
            'status' => InvitationStatus::PENDING->value,
        ]);

        $supabaseClientMock = $this->mock(SupabaseClient::class);
        $supabaseClientMock->shouldReceive('createUser')
            ->once()
            ->andReturn([
                'id' => 'supabase-user-id',
            ]);

        $this->app->instance(SupabaseClient::class, $supabaseClientMock);

        $response = $this->postJson('/api/accept-invite', [
            'token' => $invitation->token,
            'name' => 'John Doe',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Invitation accepted successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'organization_id',
                ],
            ]);

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'status' => InvitationStatus::ACCEPTED->value,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $invitation->email,
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_accept_invitation_with_invalid_token(): void
    {
        $response = $this->postJson('/api/accept-invite', [
            'token' => 'invalid-token',
            'name' => 'John Doe',
            'password' => 'password123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => true,
                'message' => 'Invalid or expired invitation.',
            ]);
    }

    public function test_accept_invitation_with_expired_status(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'status' => InvitationStatus::EXPIRED->value,
        ]);

        $response = $this->postJson('/api/accept-invite', [
            'token' => $invitation->token,
            'name' => 'John Doe',
            'password' => 'password123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => true,
                'message' => 'Invalid or expired invitation.',
            ]);
    }

    public function test_accept_invitation_with_already_accepted_status(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'status' => InvitationStatus::ACCEPTED->value,
        ]);

        $response = $this->postJson('/api/accept-invite', [
            'token' => $invitation->token,
            'name' => 'John Doe',
            'password' => 'password123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => true,
                'message' => 'Invalid or expired invitation.',
            ]);
    }
}
