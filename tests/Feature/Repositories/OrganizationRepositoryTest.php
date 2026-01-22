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

    public function test_it_filter_organizations_by_name(): void
    {
        $user = User::factory()->create();

        Organization::factory()->count(1)->create([
            'name' => $this->faker->word(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ]);

        Organization::factory()->create([
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

    public function test_it_filter_organizations_by_country(): void
    {
        $user = User::factory()->create();

        Organization::factory()->create([
            'name' => $this->faker->word(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ]);

        Organization::factory()->create([
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

    public function test_it_filter_organizations_by_status(): void
    {
        $user = User::factory()->create();

        Organization::factory()->create([
            'name' => $this->faker->word(),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ]);

        Organization::factory()->create([
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

    public function test_create_creates_organization_with_all_fields(): void
    {
        $data = [
            'name' => 'New Organization',
            'website' => 'https://www.neworg.com',
            'phone' => '+1-555-1234',
            'country' => 'United States',
            'is_active' => true,
        ];

        $repository = app(OrganizationRepository::class);
        $organization = $repository->create($data);

        $this->assertInstanceOf(Organization::class, $organization);
        $this->assertEquals('New Organization', $organization->name);
        $this->assertEquals('https://www.neworg.com', $organization->website);
        $this->assertEquals('+1-555-1234', $organization->phone);
        $this->assertEquals('United States', $organization->country);
        $this->assertTrue($organization->is_active);
        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'New Organization',
            'website' => 'https://www.neworg.com',
        ]);
    }

    public function test_create_creates_organization_with_minimal_fields(): void
    {
        $data = [
            'name' => 'Minimal Organization',
            'is_active' => false,
        ];

        $repository = app(OrganizationRepository::class);
        $organization = $repository->create($data);

        $this->assertInstanceOf(Organization::class, $organization);
        $this->assertEquals('Minimal Organization', $organization->name);
        $this->assertFalse($organization->is_active);
        $this->assertNull($organization->website);
        $this->assertNull($organization->phone);
        $this->assertNull($organization->country);
        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Minimal Organization',
            'is_active' => false,
        ]);
    }
}
