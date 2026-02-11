<?php

namespace Tests\Feature\Imports;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Imports\UserImport;
use App\Models\Organization;
use App\Services\UserService;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserImportTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private UserImport $userImport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();

        $userService = app(UserService::class);
        $this->userImport = new UserImport($this->organization->id, $userService);
    }

    /**
     * Test import a single user.
     */
    public function test_import_single_user(): void
    {
        Http::fake([
            '*auth*admin/users' => Http::response([
                'id' => 'supabase-user-1',
                'email' => 'john@example.com',
            ]),
        ]);

        $rows = collect([
            collect([
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ]),
        ]);

        $this->userImport->collection($rows);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => UserRole::USER->value,
            'organization_id' => $this->organization->id,
        ]);
    }

    /**
     * Test import multiple users.
     */
    public function test_import_multiple_users(): void
    {
        Http::fake([
            '*auth*admin/users' => Http::sequence()
                ->push(['id' => 'supabase-user-1', 'email' => 'user1@example.com'], 201)
                ->push(['id' => 'supabase-user-2', 'email' => 'user2@example.com'], 201)
                ->push(['id' => 'supabase-user-3', 'email' => 'user3@example.com'], 201),
        ]);

        $rows = collect([
            collect([
                'name' => 'User One',
                'email' => 'user1@example.com',
            ]),
            collect([
                'name' => 'User Two',
                'email' => 'user2@example.com',
            ]),
            collect([
                'name' => 'User Three',
                'email' => 'user3@example.com',
            ]),
        ]);

        $this->userImport->collection($rows);

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseHas('users', ['email' => 'user1@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'user2@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'user3@example.com']);

        Http::assertSentCount(3);
    }

    /**
     * Test import uses default password when not provided.
     */
    public function test_import_uses_default_password_when_not_provided(): void
    {
        Http::fake([
            '*auth*admin/users' => Http::response([
                'id' => 'supabase-default-pwd',
                'email' => 'test@example.com',
            ]),
        ]);

        $rows = collect([
            collect([
                'name' => 'Test User',
                'email' => 'test@example.com',
                // No password provided
            ]),
        ]);

        $this->userImport->collection($rows);

        $user = User::where('email', 'test@example.com')->first();
        // Password should be hashed version of 'asdzxc123'
        $this->assertTrue(password_verify('asdzxc123', $user->password));
    }

    /**
     * Test import uses custom password when provided.
     */
    public function test_import_uses_custom_password_when_provided(): void
    {
        Http::fake([
            '*auth*admin/users' => Http::response([
                'id' => 'supabase-custom-pwd',
                'email' => 'test@example.com',
            ]),
        ]);

        $customPassword = 'custompass123';
        $rows = collect([
            collect([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => $customPassword,
            ]),
        ]);

        $this->userImport->collection($rows);

        $user = User::where('email', 'test@example.com')->first();
        // Password should be hashed version of custom password
        $this->assertTrue(password_verify($customPassword, $user->password));
    }

    /**
     * Test all imported users get USER role.
     */
    public function test_all_imported_users_get_user_role(): void
    {
        Http::fake([
            '*auth*admin/users' => Http::sequence()
                ->push(['id' => 'supabase-user-role-1', 'email' => 'user1@example.com'], 201)
                ->push(['id' => 'supabase-user-role-2', 'email' => 'user2@example.com'], 201),
        ]);

        $rows = collect([
            collect([
                'name' => 'User One',
                'email' => 'user1@example.com',
            ]),
            collect([
                'name' => 'User Two',
                'email' => 'user2@example.com',
            ]),
        ]);

        $this->userImport->collection($rows);

        $users = User::where('organization_id', $this->organization->id)->get();
        foreach ($users as $user) {
            $this->assertEquals(UserRole::USER, $user->role);
        }
    }

    /**
     * Test imported users are assigned to correct organization.
     */
    public function test_imported_users_assigned_to_correct_organization(): void
    {
        Http::fake([
            '*auth*admin/users' => Http::sequence()
                ->push(['id' => 'supabase-org1-user', 'email' => 'user1@example.com'], 201)
                ->push(['id' => 'supabase-org2-user', 'email' => 'user2@example.com'], 201),
        ]);

        $org2 = Organization::factory()->create();

        $rows = collect([
            collect([
                'name' => 'User One',
                'email' => 'user1@example.com',
            ]),
        ]);

        // Import to first organization
        $this->userImport->collection($rows);

        // Create a new import for second organization
        $userService = app(UserService::class);
        $userImport2 = new UserImport($org2->id, $userService);

        $rows2 = collect([
            collect([
                'name' => 'User Two',
                'email' => 'user2@example.com',
            ]),
        ]);

        $userImport2->collection($rows2);

        $this->assertDatabaseHas('users', [
            'email' => 'user1@example.com',
            'organization_id' => $this->organization->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user2@example.com',
            'organization_id' => $org2->id,
        ]);
    }

    /**
     * Test validation rules for required fields.
     */
    public function test_validation_rules_require_name_and_email(): void
    {
        $rules = $this->userImport->rules();

        $this->assertArrayHasKey('*.name', $rules);
        $this->assertArrayHasKey('*.email', $rules);
        $this->assertContains('required', $rules['*.name']);
        $this->assertContains('required', $rules['*.email']);
    }

    /**
     * Test validation rules for email format.
     */
    public function test_validation_rules_require_valid_email_format(): void
    {
        $rules = $this->userImport->rules();

        $this->assertContains('email', $rules['*.email']);
    }

    /**
     * Test validation rules for email uniqueness.
     */
    public function test_validation_rules_require_unique_email(): void
    {
        $rules = $this->userImport->rules();

        $this->assertContains('unique:users,email', $rules['*.email']);
    }

    /**
     * Test chunk size is set correctly.
     */
    public function test_chunk_size_is_500(): void
    {
        $this->assertEquals(500, $this->userImport->chunkSize());
    }

    /**
     * Test import with mixed password scenarios.
     */
    public function test_import_with_mixed_password_scenarios(): void
    {
        Http::fake([
            '*auth*admin/users' => Http::sequence()
                ->push(['id' => 'supabase-mixed-custom', 'email' => 'custom@example.com'], 201)
                ->push(['id' => 'supabase-mixed-default', 'email' => 'default@example.com'], 201),
        ]);

        $customPassword = 'custompass456';
        $rows = collect([
            collect([
                'name' => 'User With Custom Password',
                'email' => 'custom@example.com',
                'password' => $customPassword,
            ]),
            collect([
                'name' => 'User With Default Password',
                'email' => 'default@example.com',
                // No password provided
            ]),
        ]);

        $this->userImport->collection($rows);

        $userWithCustom = User::where('email', 'custom@example.com')->first();
        $userWithDefault = User::where('email', 'default@example.com')->first();

        $this->assertTrue(password_verify($customPassword, $userWithCustom->password));
        $this->assertTrue(password_verify('asdzxc123', $userWithDefault->password));
    }

    /**
     * Test import preserves user data integrity.
     */
    public function test_import_preserves_user_data_integrity(): void
    {
        Http::fake([
            '*auth*admin/users' => Http::response([
                'id' => 'supabase-integrity-test',
                'email' => 'john.michael.doe@example.com',
            ]),
        ]);

        $name = 'John Michael Doe';
        $email = 'john.michael.doe@example.com';

        $rows = collect([
            collect([
                'name' => $name,
                'email' => $email,
            ]),
        ]);

        $this->userImport->collection($rows);

        $user = User::where('email', $email)->first();
        $this->assertEquals($name, $user->name);
        $this->assertEquals($email, $user->email);
        $this->assertNotNull($user->supabase_id);
    }
}
