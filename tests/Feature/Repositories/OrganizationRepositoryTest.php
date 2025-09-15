<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Repositories\OrganizationRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_it_can_filter_organizations_by_name(): void
    {
        $user = User::factory()->create();

        Organization::factory()->count(1)->create([
            'user_id' => $user->id,
            'name' => $this->faker->word(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ]);

        Organization::factory()->create([
            'user_id' => $user->id,
            'name' => 'High Risk AI',
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ]);
        $repository = app(OrganizationRepository::class);
        $results = $repository->getFilteredOrganizations(['name' => 'Risk']);

        $this->assertCount(1, $results);
        $this->assertEquals('High Risk AI', $results->first()->name);
    }

    public function test_it_can_filter_organizations_by_country(): void
    {
        $user = User::factory()->create();

        Organization::factory()->create([
            'user_id' => $user->id,
            'name' => $this->faker->word(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ]);

        Organization::factory()->create([
            'user_id' => $user->id,
            'name' => $this->faker->word(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => 'Pakistan',
            'is_active' => true,
        ]);
        $repository = app(OrganizationRepository::class);
        $results = $repository->getFilteredOrganizations(['country' => 'Pakistan']);

        $this->assertCount(1, $results);
        $this->assertEquals('Pakistan', $results->first()->country);
    }

    public function test_it_can_filter_organizations_by_status(): void
    {
        $user = User::factory()->create();

        Organization::factory()->create([
            'user_id' => $user->id,
            'name' => $this->faker->word(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ]);

        Organization::factory()->create([
            'user_id' => $user->id,
            'name' => $this->faker->word(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => 'Pakistan',
            'is_active' => false,
        ]);
        $repository = app(OrganizationRepository::class);
        $results = $repository->getFilteredOrganizations(['is_active' => true]);

        $this->assertCount(1, $results);
    }
}
