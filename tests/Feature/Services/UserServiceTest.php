<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Services\UserService;
use App\Clients\SupabaseClient;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();

        // Bind mock to service container
        $this->app->bind(SupabaseClient::class, function () {
            return $this->mock(SupabaseClient::class);
        });
    }

    private function getUserService(): UserService
    {
        return app(UserService::class);
    }

    private function getSupabaseClientMock()
    {
        return app(SupabaseClient::class);
    }

    /**
     * Test creating a user with role and organization.
     */
    public function test_create_user_with_role_and_organization(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ];

        $supabaseUserId = 'supabase-user-123';

        $this->getSupabaseClientMock()
            ->shouldReceive('createUser')
            ->once()
            ->with([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret123',
                'role' => UserRole::ADMIN->value,
            ])
            ->andReturn(['id' => $supabaseUserId]);

        $user = $this->getUserService()->createUserWithRoleAndOrganization(
            $userData,
            UserRole::ADMIN,
            $this->organization->id
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals(UserRole::ADMIN, $user->role);
        $this->assertEquals($supabaseUserId, $user->supabase_id);
        $this->assertEquals($this->organization->id, $user->organization_id);
    }

    /**
     * Test creating admin for organization.
     */
    public function test_create_admin_for_organization(): void
    {
        $adminData = [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'adminpass123',
        ];

        $supabaseUserId = 'supabase-admin-456';

        $this->getSupabaseClientMock()
            ->shouldReceive('createUser')
            ->once()
            ->with([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => 'adminpass123',
                'role' => UserRole::ADMIN->value,
            ])
            ->andReturn(['id' => $supabaseUserId]);

        $admin = $this->getUserService()->createAdminForOrganization(
            $adminData,
            $this->organization->id
        );

        $this->assertInstanceOf(User::class, $admin);
        $this->assertEquals('Admin User', $admin->name);
        $this->assertEquals('admin@example.com', $admin->email);
        $this->assertEquals(UserRole::ADMIN, $admin->role);
        $this->assertEquals($supabaseUserId, $admin->supabase_id);
        $this->assertEquals($this->organization->id, $admin->organization_id);
    }

    /**
     * Test creating regular user for organization.
     */
    public function test_create_user_for_organization(): void
    {
        $userData = [
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'userpass123',
        ];

        $supabaseUserId = 'supabase-user-789';

        $this->getSupabaseClientMock()
            ->shouldReceive('createUser')
            ->once()
            ->with([
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'password' => 'userpass123',
                'role' => UserRole::USER->value,
            ])
            ->andReturn(['id' => $supabaseUserId]);

        $user = $this->getUserService()->createUserForOrganization(
            $userData,
            $this->organization->id
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Regular User', $user->name);
        $this->assertEquals('user@example.com', $user->email);
        $this->assertEquals(UserRole::USER, $user->role);
        $this->assertEquals($supabaseUserId, $user->supabase_id);
        $this->assertEquals($this->organization->id, $user->organization_id);
    }

    /**
     * Test that user is persisted to database.
     */
    public function test_user_is_persisted_to_database(): void
    {
        $userData = [
            'name' => 'Database Test User',
            'email' => 'db-test@example.com',
            'password' => 'dbpass123',
        ];

        $supabaseUserId = 'supabase-db-test';

        $this->getSupabaseClientMock()
            ->shouldReceive('createUser')
            ->andReturn(['id' => $supabaseUserId]);

        $user = $this->getUserService()->createAdminForOrganization(
            $userData,
            $this->organization->id
        );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Database Test User',
            'email' => 'db-test@example.com',
            'role' => UserRole::ADMIN->value,
            'supabase_id' => $supabaseUserId,
            'organization_id' => $this->organization->id,
        ]);
    }

    /**
     * Test that password is hashed when storing in database.
     */
    public function test_password_is_hashed(): void
    {
        $plainPassword = 'plaintext123';
        $userData = [
            'name' => 'Password Test',
            'email' => 'password@example.com',
            'password' => $plainPassword,
        ];

        $this->getSupabaseClientMock()
            ->shouldReceive('createUser')
            ->andReturn(['id' => 'supabase-pwd-test']);

        $user = $this->getUserService()->createUserForOrganization(
            $userData,
            $this->organization->id
        );

        // Password should be hashed, not equal to plain text
        $this->assertNotEquals($plainPassword, $user->password);
        // But it should still validate
        $this->assertTrue(password_verify($plainPassword, $user->password));
    }

    /**
     * Test multiple users can be created in the same organization.
     */
    public function test_multiple_users_in_same_organization(): void
    {
        $this->getSupabaseClientMock()
            ->shouldReceive('createUser')
            ->andReturn(['id' => 'supabase-id-1'], ['id' => 'supabase-id-2']);

        $user1 = $this->getUserService()->createAdminForOrganization(
            ['name' => 'Admin 1', 'email' => 'admin1@example.com', 'password' => 'pass1'],
            $this->organization->id
        );

        $user2 = $this->getUserService()->createUserForOrganization(
            ['name' => 'User 1', 'email' => 'user1@example.com', 'password' => 'pass2'],
            $this->organization->id
        );

        $this->assertDatabaseCount('users', 2);
        $this->assertEquals($this->organization->id, $user1->organization_id);
        $this->assertEquals($this->organization->id, $user2->organization_id);
        $this->assertEquals(UserRole::ADMIN, $user1->role);
        $this->assertEquals(UserRole::USER, $user2->role);
    }

    /**
     * Test users can be created in different organizations.
     */
    public function test_users_in_different_organizations(): void
    {
        $org2 = Organization::factory()->create();

        $this->getSupabaseClientMock()
            ->shouldReceive('createUser')
            ->andReturn(['id' => 'supabase-org1'], ['id' => 'supabase-org2']);

        $user1 = $this->getUserService()->createAdminForOrganization(
            ['name' => 'Org1 Admin', 'email' => 'org1@example.com', 'password' => 'pass1'],
            $this->organization->id
        );

        $user2 = $this->getUserService()->createAdminForOrganization(
            ['name' => 'Org2 Admin', 'email' => 'org2@example.com', 'password' => 'pass2'],
            $org2->id
        );

        $this->assertEquals($this->organization->id, $user1->organization_id);
        $this->assertEquals($org2->id, $user2->organization_id);
        $this->assertNotEquals($user1->organization_id, $user2->organization_id);
    }
}
