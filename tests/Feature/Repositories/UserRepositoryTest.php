<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Repositories\UserRepository;
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
}
