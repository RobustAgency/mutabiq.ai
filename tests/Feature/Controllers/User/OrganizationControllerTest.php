<?php

namespace Tests\Feature\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_view_their_organization_with_members(): void
    {
        $user = $this->createUserWithOrganizationAndMembers();

        $response = $this->actingAs($user)->getJson('/api/organizations');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Organizations retrieved successfully',
            'data' => [
                'id' => $user->organization->id,
                'name' => $user->organization->name,
                'website' => $user->organization->website,
                'phone' => $user->organization->phone,
                'country' => $user->organization->country,
                'is_active' => $user->organization->is_active,
                'created_at' => $user->organization->created_at->format('Y-m-d\TH:i:sP'),
                'updated_at' => $user->organization->updated_at->format('Y-m-d\TH:i:sP'),
                'members' => $user->organization->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'organization_id' => $member->organization_id,
                        'is_organization_active' => true,
                        'role' => is_object($member->role) ? $member->role->value : $member->role,
                        'created_at' => $member->created_at->format('Y-m-d\TH:i:sP'),
                        'updated_at' => $member->updated_at->format('Y-m-d\TH:i:sP'),
                    ];
                })->toArray(),
            ],
        ]);
    }

    public function test_user_can_create_organization(): void
    {
        $user = User::factory()->create(['role' => UserRole::OWNER]);

        $organizationData = [
            'name' => $this->faker->company(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ];

        $response = $this->actingAs($user)->postJson('/api/organizations', $organizationData);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Organization created successfully',
            'data' => null,
        ]);

        $this->assertDatabaseHas('organizations', [
            'name' => $organizationData['name'],
            'website' => $organizationData['website'],
            'phone' => $organizationData['phone'],
            'country' => $organizationData['country'],
            'user_id' => $user->id,
        ]);
    }

    private function createUserWithOrganizationAndMembers()
    {
        $user = User::factory()->create(['role' => UserRole::OWNER]);
        $organization = Organization::factory()->create(['user_id' => $user->id]);
        $user->update(['organization_id' => $organization->id]);

        User::factory()->count(2)->create(['organization_id' => $organization->id]);

        return $user;
    }
}
