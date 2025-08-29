<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use Tests\Fakes\FakeSupabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_user_can_view_profile(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'id' => 1,
            'role' => UserRole::USER,
        ]);

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertOk();

        $responseData = $response->json();
        $this->assertFalse($responseData['error']);
        $this->assertEquals('Profile retrieved successfully.', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($user->id, $responseData['data']['id']);
        $this->assertEquals($user->email, $responseData['data']['email']);
        $this->assertEquals($user->name, $responseData['data']['name']);
    }
}
