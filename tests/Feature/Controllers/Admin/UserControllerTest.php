<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use Tests\Fakes\FakeSupabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_super_admin_can_view_all_users_with_pagination(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        $users = User::factory()->count(5)->create(['role' => UserRole::OWNER]);

        foreach ($users as $user) {
            $user->created_at = now();
            $user->updated_at = now();
            $user->save();
        }
        $response = $this->actingAs($admin)->getJson('/api/admin/users?role=owner');

        $response->assertOk();

        $responseData = $response->json();
        $this->assertFalse($responseData['error']);
        $this->assertEquals('Users retrieved successfully', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);

        foreach ($responseData['data']['data'] as $user) {
            $this->assertEquals(UserRole::OWNER->value, $user['role']);
        }
    }

    public function test_super_admin_can_store_new_admin_user(): void
    {
        Notification::fake();

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
        $admin = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        $payload = [
            'name' => fake()->name(),
            'email' => 'new.admin@example.com',
            'password' => fake()->password(8),
            'role' => UserRole::ADMIN->value,
        ];

        $response = $this->actingAs($admin)->postJson('/api/admin/users', $payload);

        $response->assertCreated();
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);

        $responseData = $response->json();

        $this->assertFalse($responseData['error']);
        $this->assertEquals('Admin user created successfully', $responseData['message']);
        $this->assertEquals('new.admin@example.com', $responseData['data']['email']);

        $this->assertDatabaseHas('users', [
            'email' => 'new.admin@example.com',
            'role' => UserRole::ADMIN->value,
        ]);
    }

    public function test_super_admin_can_view_user(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $user = User::factory()->create(['role' => UserRole::OWNER]);

        $response = $this->actingAs($admin)->getJson("/api/admin/users/{$user->id}");
        $response->assertOk();
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);

        $responseData = $response->json();
        $this->assertFalse($responseData['error']);
        $this->assertEquals('User retrieved successfully', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    public function test_super_admin_can_search_users_by_name(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        User::factory()->create([
            'role' => UserRole::OWNER,
            'name' => 'John Doe',
        ]);

        User::factory()->create([
            'role' => UserRole::OWNER,
            'name' => 'Jane Smith',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/admin/users/search?term=John');

        $response->assertOk();

        $responseData = $response->json();
        $this->assertFalse($responseData['error']);
        $this->assertEquals('Users retrieved successfully', $responseData['message']);
        $this->assertEquals('John Doe', $responseData['data'][0]['name']);
    }

    public function test_super_admin_can_search_users_by_email(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        User::factory()->create([
            'role' => UserRole::OWNER,
            'email' => 'john.doe@example.com',
        ]);

        User::factory()->create([
            'role' => UserRole::OWNER,
            'email' => 'jane.smith@example.com',
        ]);
        $response = $this->actingAs($admin)->getJson('/api/admin/users/search?term=john.doe');

        $response->assertOk();
        $responseData = $response->json();
        $this->assertFalse($responseData['error']);
        $this->assertEquals('john.doe@example.com', $responseData['data'][0]['email']);
    }
}
