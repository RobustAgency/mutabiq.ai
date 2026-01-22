<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use Tests\Fakes\FakeSupabase;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = app(UserRepository::class);
    }

    public function test_it_get_users_by_organization_id(): void
    {
        $organization = Organization::factory()->create();
        $organizationID = $organization->id;

        User::factory()->count(3)->create(['organization_id' => $organizationID]);

        $users = $this->userRepository->getUsersByOrganizationID($organizationID, 10);

        $this->assertCount(3, $users);
        foreach ($users as $user) {
            $this->assertEquals($organizationID, $user->organization_id);
        }
    }

    public function test_create_admin_successfully(): void
    {
        Http::fake([
            '*/auth/v1/admin/users' => function ($request) {
                $requestData = $request->data();

                return Http::response(FakeSupabase::getUserCreationResponse([
                    'email' => $requestData['email'],
                    'name' => $requestData['user_metadata']['name'] ?? 'Test Admin',
                ]), 200);
            },
        ]);

        $adminData = [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ];

        $user = $this->userRepository->createAdmin($adminData);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'name' => 'Admin User',
            'role' => UserRole::ADMIN->value,
        ]);
        $this->assertEquals(UserRole::ADMIN, $user->role);
        $this->assertNotNull($user->supabase_id);
    }

    public function test_create_admin_for_organization(): void
    {
        Http::fake([
            '*/auth/v1/admin/users' => function ($request) {
                $requestData = $request->data();

                return Http::response(FakeSupabase::getUserCreationResponse([
                    'email' => $requestData['email'],
                    'name' => $requestData['user_metadata']['name'] ?? 'Test Admin',
                ]), 200);
            },
        ]);

        $organization = Organization::factory()->create();
        $adminData = [
            'name' => 'Organization Admin',
            'email' => 'org.admin@example.com',
            'password' => 'password123',
        ];

        $admin = $this->userRepository->createAdminForOrganization($adminData, $organization);

        $this->assertDatabaseHas('users', [
            'email' => 'org.admin@example.com',
            'name' => 'Organization Admin',
            'role' => UserRole::ADMIN->value,
            'organization_id' => $organization->id,
        ]);
        $this->assertEquals($organization->id, $admin->organization_id);
        $this->assertEquals(UserRole::ADMIN, $admin->role);
    }

    public function test_get_admin_by_organization_id(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => UserRole::ADMIN->value,
        ]);

        // Create other users to ensure correct filtering
        User::factory()->create([
            'organization_id' => $organization->id,
            'role' => UserRole::CONTRIBUTOR->value,
        ]);
        User::factory()->create([
            'role' => UserRole::ADMIN->value,
        ]);

        $retrievedAdmin = $this->userRepository->getAdminByOrganizationID($organization->id);

        $this->assertEquals($admin->id, $retrievedAdmin->id);
        $this->assertEquals(UserRole::ADMIN, $retrievedAdmin->role);
        $this->assertEquals($organization->id, $retrievedAdmin->organization_id);
    }
}
