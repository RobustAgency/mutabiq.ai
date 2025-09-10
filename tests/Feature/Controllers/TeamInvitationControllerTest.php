<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use Tests\Fakes\FakeSupabase;
use App\Models\TeamInvitation;
use App\Enums\InvitationStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamInvitationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_organizer_can_invite_members(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => UserRole::OWNER, 'organization_id' => $organization->id]);

        $payload = [
            'members' => [
                ['email' => $this->faker->email, 'role' => UserRole::PROJECT_LEAD->value],
                ['email' => $this->faker->email, 'role' => UserRole::REVIEWER->value],
            ],
        ];
        $response = $this->actingAs($user)->postJson('/api/invite-members', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Invitations sent successfully',
        ]);
        $this->assertDatabaseCount('team_invitations', 2);
        $this->assertDatabaseHas('team_invitations', [
            'email' => $payload['members'][0]['email'],
            'role' => $payload['members'][0]['role'],
        ]);
        $this->assertDatabaseHas('team_invitations', [
            'email' => $payload['members'][1]['email'],
            'role' => $payload['members'][1]['role'],
        ]);
    }

    public function test_invitee_can_accept_invitation(): void
    {
        $organization = Organization::factory()->create();
        $owner = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => UserRole::OWNER,
        ]);

        $invite = TeamInvitation::factory()->create([
            'organization_id' => $organization->id,
            'invited_by' => $owner->id,
            'email' => 'newuser@example.com',
            'role' => UserRole::CONTRIBUTOR,
            'status' => InvitationStatus::PENDING,
        ]);

        Http::fake([
            '*/auth/v1/admin/users' => function ($request) {
                $requestData = $request->data();

                return Http::response(FakeSupabase::getUserCreationResponse([
                    'email' => $requestData['email'],
                    'name' => $requestData['user_metadata']['name'] ?? 'Test User',
                    'email_verified' => $requestData['email_confirm'] ?? true,
                ]), 200);
            },
        ]);

        $payload = [
            'name' => 'New User',
            'password' => 'password123',
            'token' => $invite->token,
        ];

        $response = $this->postJson('/api/accept-invite', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Invitation accepted successfully',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'organization_id' => $organization->id,
        ]);

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invite->id,
            'status' => InvitationStatus::ACCEPTED,
        ]);
    }
}
