<?php

namespace Tests\Feature\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;


class MemberControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_update_member()
    {
        Notification::fake();

        Http::fake([
            '*/auth/v1/admin/users/*' => function ($request) {
                $requestData = $request->data();

                return Http::response([
                    'id' => 'fake-supabase-id',
                    'email' => $requestData['email'] ?? 'updated.member@example.com',
                    'user_metadata' => [
                        'name' => $requestData['name'] ?? 'Updated Member',
                    ],
                ], 200);
            },
        ]);

        $user = $this->createUserWithOrganizationAndMembers();
        $member = $user->organization->members->first();
        $member->update(['supabase_id' => 'fake-supabase-id']);

        $updateData = [
            'name' => $this->faker->name(),
            'role' => UserRole::AUDITOR,
        ];

        $response = $this->actingAs($user)->putJson("/api/members/{$member->id}", $updateData);

        $response->assertOk();
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'organization_id',
                'is_organization_active',
                'role',
                'created_at',
                'updated_at',
            ],
        ]);

        $responseData = $response->json();

        $this->assertFalse($responseData['error']);
        $this->assertEquals('Member updated successfully', $responseData['message']);
        $this->assertEquals($updateData['name'], $responseData['data']['name']);
        $this->assertEquals($member->email, $responseData['data']['email']);
        $this->assertEquals($member->organization_id, $responseData['data']['organization_id']);
        $this->assertEquals('auditor', $responseData['data']['role']);

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'email' => $member->email,
            'name' => $updateData['name'],
            'role' => $updateData['role'],
        ]);
    }

    public function test_user_can_delete_member()
    {
        Notification::fake();

        Http::fake([
            '*/auth/v1/admin/users/*' => Http::response([], 200), // fake delete success
        ]);

        $user = $this->createUserWithOrganizationAndMembers();
        $member = $user->organization->members->first();

        // attach supabase id to member
        $member->update(['supabase_id' => 'fake-supabase-id']);

        $response = $this->actingAs($user)->deleteJson("/api/members/{$member->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Member deleted successfully',
            'data' => null,
        ]);

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    private function createUserWithOrganizationAndMembers()
    {
        $user = User::factory()->create(['role' => UserRole::OWNER->value]);
        $organization = Organization::factory()->create(['user_id' => $user->id]);
        $user->update(['organization_id' => $organization->id]);

        // Create additional members
        User::factory()->count(2)->create([
            'organization_id' => $organization->id,
            'role' => UserRole::CONTRIBUTOR,
        ]);

        return $user;
    }
}
