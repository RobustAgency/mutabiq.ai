<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Enums\Stakeholder\Classification;
use App\Repositories\StakeholderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StakeholderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected StakeholderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StakeholderRepository;
    }

    public function test_get_filtered_stakeholders_returns_paginated_results(): void
    {
        Stakeholder::factory()->count(15)->create();

        $result = $this->repository->getFilteredStakeholders(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_get_filtered_stakeholders_with_default_pagination(): void
    {
        Stakeholder::factory()->count(5)->create();

        $result = $this->repository->getFilteredStakeholders();

        $this->assertCount(5, $result->items());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_get_filtered_stakeholders_with_organization_id(): void
    {
        $organization = Organization::factory()->create();
        Stakeholder::factory()->count(8)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredStakeholders(['organization_id' => $organization->id]);

        $this->assertCount(8, $result->items());
    }

    public function test_filter_by_type(): void
    {
        Stakeholder::factory()->create(['type' => 'internal']);
        Stakeholder::factory()->create(['type' => 'external']);
        Stakeholder::factory()->create(['type' => 'internal']);

        $result = $this->repository->getFilteredStakeholders(['type' => 'internal']);

        $this->assertCount(2, $result->items());
        $types = collect($result->items())->pluck('type')->unique()->toArray();
        $this->assertEquals(['internal'], $types);
    }

    public function test_filter_with_empty_type_returns_all(): void
    {
        Stakeholder::factory()->create(['type' => 'internal']);
        Stakeholder::factory()->create(['type' => 'external']);

        $result = $this->repository->getFilteredStakeholders(['type' => '']);

        $this->assertCount(2, $result->items());
    }

    public function test_results_are_sorted_by_latest(): void
    {
        $oldest = Stakeholder::factory()->create(['created_at' => now()->subDays(3)]);
        $middle = Stakeholder::factory()->create(['created_at' => now()->subDays(2)]);
        $newest = Stakeholder::factory()->create(['created_at' => now()->subDay()]);

        $result = $this->repository->getFilteredStakeholders();

        $items = $result->items();
        $this->assertEquals($newest->id, $items[0]->id);
        $this->assertEquals($middle->id, $items[1]->id);
        $this->assertEquals($oldest->id, $items[2]->id);
    }

    public function test_create_stores_stakeholder(): void
    {
        $data = [
            'organization_id' => Organization::factory()->create()->id,
            'type' => 'internal',
            'display_name' => 'John Smith',
            'legal_name' => 'Tech Company',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'org_unit' => 'Engineering',
            'role_tags' => ['admin', 'developer'],
            'timezone' => 'America/New_York',
            'classification' => 'internal',
            'country' => 'US',
            'active' => true,
        ];

        $stakeholder = $this->repository->create($data);

        $this->assertInstanceOf(Stakeholder::class, $stakeholder);
        $this->assertEquals('John Smith', $stakeholder->display_name);
        $this->assertEquals('john@example.com', $stakeholder->email);
        $this->assertDatabaseHas('stakeholders', [
            'display_name' => 'John Smith',
            'email' => 'john@example.com',
        ]);
    }

    public function test_update_modifies_stakeholder(): void
    {
        $stakeholder = Stakeholder::factory()->create([
            'display_name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $updateData = [
            'display_name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $updated = $this->repository->update($stakeholder, $updateData);

        $this->assertEquals('Updated Name', $updated->display_name);
        $this->assertEquals('updated@example.com', $updated->email);
        $this->assertDatabaseHas('stakeholders', [
            'id' => $stakeholder->id,
            'display_name' => 'Updated Name',
        ]);
    }

    public function test_pagination_works_with_type_filter(): void
    {
        Stakeholder::factory()->count(12)->create(['type' => 'internal']);
        Stakeholder::factory()->count(8)->create(['type' => 'external']);

        $result = $this->repository->getFilteredStakeholders([
            'type' => 'internal',
            'per_page' => 5,
        ]);

        $this->assertCount(5, $result->items());
        $this->assertEquals(12, $result->total());
    }

    public function test_filter_by_display_name(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'Jane Doe']);
        Stakeholder::factory()->create(['display_name' => 'John Williams']);

        $result = $this->repository->getFilteredStakeholders(['name' => 'John']);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $stakeholder) {
            $this->assertStringContainsString('John', $stakeholder->display_name);
        }
    }

    public function test_filter_by_name_is_case_insensitive(): void
    {
        Stakeholder::factory()->create(['display_name' => 'JOHN SMITH']);
        Stakeholder::factory()->create(['display_name' => 'jane doe']);
        Stakeholder::factory()->create(['display_name' => 'John Williams']);

        $result = $this->repository->getFilteredStakeholders(['name' => 'john']);

        $this->assertCount(2, $result->items());
    }

    public function test_filter_by_name_with_partial_match(): void
    {
        Stakeholder::factory()->create(['display_name' => 'Software Engineer']);
        Stakeholder::factory()->create(['display_name' => 'Senior Software Developer']);
        Stakeholder::factory()->create(['display_name' => 'Hardware Technician']);

        $result = $this->repository->getFilteredStakeholders(['name' => 'Software']);

        $this->assertCount(2, $result->items());
    }

    public function test_filter_by_multiple_filters(): void
    {
        $organization = Organization::factory()->create();

        Stakeholder::factory()->create([
            'organization_id' => $organization->id,
            'type' => 'internal',
            'display_name' => 'John Smith',
        ]);
        Stakeholder::factory()->create([
            'organization_id' => $organization->id,
            'type' => 'external',
            'display_name' => 'John Doe',
        ]);
        Stakeholder::factory()->create([
            'organization_id' => $organization->id,
            'type' => 'internal',
            'display_name' => 'Jane Williams',
        ]);

        $result = $this->repository->getFilteredStakeholders([
            'organization_id' => $organization->id,
            'type' => 'internal',
            'name' => 'John',
        ]);

        $this->assertCount(1, $result->items());
        $stakeholder = $result->items()[0];
        $this->assertEquals($organization->id, $stakeholder->organization_id);
        $this->assertEquals('internal', $stakeholder->type);
        $this->assertStringContainsString('John', $stakeholder->display_name);
    }

    public function test_filter_returns_empty_when_no_matches(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'Jane Doe']);

        $result = $this->repository->getFilteredStakeholders(['name' => 'NonExistent']);

        $this->assertCount(0, $result->items());
    }

    public function test_filter_by_organization_with_different_orgs(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        Stakeholder::factory()->count(5)->create(['organization_id' => $org1->id]);
        Stakeholder::factory()->count(3)->create(['organization_id' => $org2->id]);

        $result1 = $this->repository->getFilteredStakeholders(['organization_id' => $org1->id]);
        $result2 = $this->repository->getFilteredStakeholders(['organization_id' => $org2->id]);

        $this->assertEquals(5, $result1->total());
        $this->assertEquals(3, $result2->total());
    }

    public function test_filter_by_type_with_different_types(): void
    {
        Stakeholder::factory()->count(7)->create(['type' => 'internal']);
        Stakeholder::factory()->count(4)->create(['type' => 'external']);
        Stakeholder::factory()->count(2)->create(['type' => 'partner']);

        $internalResult = $this->repository->getFilteredStakeholders(['type' => 'internal']);
        $externalResult = $this->repository->getFilteredStakeholders(['type' => 'external']);
        $partnerResult = $this->repository->getFilteredStakeholders(['type' => 'partner']);

        $this->assertEquals(7, $internalResult->total());
        $this->assertEquals(4, $externalResult->total());
        $this->assertEquals(2, $partnerResult->total());
    }

    public function test_filter_with_custom_per_page(): void
    {
        Stakeholder::factory()->count(25)->create(['type' => 'internal']);

        $result = $this->repository->getFilteredStakeholders([
            'type' => 'internal',
            'per_page' => 7,
        ]);

        $this->assertCount(7, $result->items());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(7, $result->perPage());
        $this->assertEquals(4, $result->lastPage());
    }

    public function test_filters_maintain_latest_order(): void
    {
        $oldest = Stakeholder::factory()->create([
            'type' => 'internal',
            'created_at' => now()->subDays(5),
        ]);
        $middle = Stakeholder::factory()->create([
            'type' => 'internal',
            'created_at' => now()->subDays(3),
        ]);
        $newest = Stakeholder::factory()->create([
            'type' => 'internal',
            'created_at' => now()->subDay(),
        ]);

        $result = $this->repository->getFilteredStakeholders(['type' => 'internal']);

        $items = $result->items();
        $this->assertEquals($newest->id, $items[0]->id);
        $this->assertEquals($middle->id, $items[1]->id);
        $this->assertEquals($oldest->id, $items[2]->id);
    }

    public function test_get_statistics_returns_correct_total_count(): void
    {
        $organization = Organization::factory()->create();
        Stakeholder::factory()->count(10)->create(['organization_id' => $organization->id]);

        $stats = $this->repository->getStatistics($organization->id);

        $this->assertEquals(10, $stats['total_count']);
    }

    public function test_get_statistics_counts_internal_stakeholders(): void
    {
        $organization = Organization::factory()->create();
        Stakeholder::factory()->count(7)->create([
            'organization_id' => $organization->id,
            'classification' => Classification::INTERNAL->value,
        ]);
        Stakeholder::factory()->count(3)->create([
            'organization_id' => $organization->id,
            'classification' => Classification::EXTERNAL->value,
        ]);

        $stats = $this->repository->getStatistics($organization->id);

        $this->assertEquals(7, $stats['internal_count']);
    }

    public function test_get_statistics_counts_external_stakeholders(): void
    {
        $organization = Organization::factory()->create();
        Stakeholder::factory()->count(5)->create([
            'organization_id' => $organization->id,
            'classification' => Classification::INTERNAL->value,
        ]);
        Stakeholder::factory()->count(8)->create([
            'organization_id' => $organization->id,
            'classification' => Classification::EXTERNAL->value,
        ]);

        $stats = $this->repository->getStatistics($organization->id);

        $this->assertEquals(8, $stats['external_count']);
    }

    public function test_get_statistics_returns_all_counts(): void
    {
        $organization = Organization::factory()->create();
        Stakeholder::factory()->count(6)->create([
            'organization_id' => $organization->id,
            'classification' => Classification::INTERNAL->value,
        ]);
        Stakeholder::factory()->count(4)->create([
            'organization_id' => $organization->id,
            'classification' => Classification::EXTERNAL->value,
        ]);

        $stats = $this->repository->getStatistics($organization->id);

        $this->assertEquals(10, $stats['total_count']);
        $this->assertEquals(6, $stats['internal_count']);
        $this->assertEquals(4, $stats['external_count']);
    }
}
