<?php

namespace Tests\Feature\Repositories;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Repositories\UserRepository;
use Tests\TestCase;

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

        $users = $this->userRepository->getUsersByOrganizationID($organizationID);

        $this->assertCount(3, $users);
        foreach ($users as $user) {
            $this->assertEquals($organizationID, $user->organization_id);
        }
    }
}
