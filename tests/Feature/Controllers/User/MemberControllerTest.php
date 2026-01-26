<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_delete_member(): void
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

    public function test_user_can_list_members(): void
    {
        $user = $this->createUserWithOrganizationAndMembers();

        $response = $this->actingAs($user)->getJson('/api/members');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'organization_id',
                        'role',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'current_page',
            ],
        ]);

        $responseData = $response->json();

        $this->assertFalse($responseData['error']);
        $this->assertEquals('Members retrieved successfully', $responseData['message']);
        $this->assertCount(3, $responseData['data']['data']);

        foreach ($responseData['data']['data'] as $member) {
            $this->assertArrayHasKey('id', $member);
            $this->assertArrayHasKey('name', $member);
            $this->assertArrayHasKey('email', $member);
            $this->assertArrayHasKey('organization_id', $member);
            $this->assertArrayHasKey('role', $member);
            $this->assertArrayHasKey('created_at', $member);
            $this->assertArrayHasKey('updated_at', $member);
        }
    }

    private function createUserWithOrganizationAndMembers(): User
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $organization = Organization::factory()->create();
        $user->update(['organization_id' => $organization->id]);

        // Create additional members
        User::factory()->count(2)->create([
            'organization_id' => $organization->id,
            'role' => UserRole::CONTRIBUTOR,
        ]);

        return $user;
    }
}
