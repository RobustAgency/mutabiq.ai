<?php

namespace Tests\Feature\Repositories;

use App\Models\Stakeholder;
use App\Repositories\StakeholderRepository;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StakeholderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected StakeholderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StakeholderRepository();
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

    public function test_search_by_display_name(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'Jane Doe']);
        Stakeholder::factory()->create(['display_name' => 'John Johnson']);

        $result = $this->repository->getFilteredStakeholders(['search' => 'John']);

        $this->assertCount(2, $result->items());
        $displayNames = collect($result->items())->pluck('display_name')->toArray();
        $this->assertTrue(in_array('John Smith', $displayNames));
        $this->assertTrue(in_array('John Johnson', $displayNames));
        $this->assertFalse(in_array('Jane Doe', $displayNames));
    }

    public function test_search_by_legal_name(): void
    {
        Stakeholder::factory()->create([
            'display_name' => 'Person A',
            'legal_name' => 'Acme Corporation',
        ]);
        Stakeholder::factory()->create([
            'display_name' => 'Person B',
            'legal_name' => 'Beta Industries',
        ]);
        Stakeholder::factory()->create([
            'display_name' => 'Person C',
            'legal_name' => 'Acme Solutions',
        ]);

        $result = $this->repository->getFilteredStakeholders(['search' => 'Acme']);

        $this->assertCount(2, $result->items());
        $legalNames = collect($result->items())->pluck('legal_name')->toArray();
        $this->assertTrue(in_array('Acme Corporation', $legalNames));
        $this->assertTrue(in_array('Acme Solutions', $legalNames));
    }

    public function test_search_by_email(): void
    {
        Stakeholder::factory()->create(['email' => 'john@example.com']);
        Stakeholder::factory()->create(['email' => 'jane@example.com']);
        Stakeholder::factory()->create(['email' => 'admin@company.com']);

        $result = $this->repository->getFilteredStakeholders(['search' => 'example']);

        $this->assertCount(2, $result->items());
        $emails = collect($result->items())->pluck('email')->toArray();
        $this->assertTrue(in_array('john@example.com', $emails));
        $this->assertTrue(in_array('jane@example.com', $emails));
    }

    public function test_search_is_case_insensitive(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'JANE DOE']);
        Stakeholder::factory()->create(['display_name' => 'alice johnson']);

        $resultUpperCase = $this->repository->getFilteredStakeholders(['search' => 'JOHN']);
        $resultLowerCase = $this->repository->getFilteredStakeholders(['search' => 'john']);

        $this->assertCount(2, $resultUpperCase->items());
        $this->assertCount(2, $resultLowerCase->items());
    }

    public function test_search_with_partial_match(): void
    {
        Stakeholder::factory()->create(['display_name' => 'Robert Johnson']);
        Stakeholder::factory()->create(['display_name' => 'Rob Smith']);
        Stakeholder::factory()->create(['display_name' => 'Alice Brown']);

        $result = $this->repository->getFilteredStakeholders(['search' => 'Rob']);

        $this->assertCount(2, $result->items());
        $displayNames = collect($result->items())->pluck('display_name')->toArray();
        $this->assertTrue(in_array('Robert Johnson', $displayNames));
        $this->assertTrue(in_array('Rob Smith', $displayNames));
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'Jane Doe']);

        $result = $this->repository->getFilteredStakeholders(['search' => 'NonExistentName']);

        $this->assertCount(0, $result->items());
    }

    public function test_search_across_multiple_fields(): void
    {
        Stakeholder::factory()->create([
            'display_name' => 'John Smith',
            'legal_name' => 'Different Corp',
            'email' => 'john@example.com',
        ]);
        Stakeholder::factory()->create([
            'display_name' => 'Jane Doe',
            'legal_name' => 'Tech Solutions',
            'email' => 'jane@tech.com',
        ]);
        Stakeholder::factory()->create([
            'display_name' => 'Bob Wilson',
            'legal_name' => 'Other Company',
            'email' => 'bob@tech.com',
        ]);

        $result = $this->repository->getFilteredStakeholders(['search' => 'Tech']);

        $this->assertCount(2, $result->items());
        $items = collect($result->items());
        $this->assertTrue($items->contains('display_name', 'Jane Doe'));
        $this->assertTrue($items->contains('display_name', 'Bob Wilson'));
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

    public function test_filter_by_type_and_search_combined(): void
    {
        Stakeholder::factory()->create([
            'type' => 'internal',
            'display_name' => 'John Smith',
        ]);
        Stakeholder::factory()->create([
            'type' => 'external',
            'display_name' => 'John Doe',
        ]);
        Stakeholder::factory()->create([
            'type' => 'internal',
            'display_name' => 'Alice Johnson',
        ]);

        $result = $this->repository->getFilteredStakeholders([
            'type' => 'internal',
            'search' => 'John',
        ]);

        $this->assertCount(2, $result->items());
        $item = $result->items()[0];
        $this->assertEquals('John Smith', $item->display_name);
        $this->assertEquals('internal', $item->type);
    }

    public function test_filter_with_empty_type_returns_all(): void
    {
        Stakeholder::factory()->create(['type' => 'internal']);
        Stakeholder::factory()->create(['type' => 'external']);

        $result = $this->repository->getFilteredStakeholders(['type' => '']);

        $this->assertCount(2, $result->items());
    }

    public function test_filter_with_empty_search_returns_all(): void
    {
        Stakeholder::factory()->count(3)->create();

        $result = $this->repository->getFilteredStakeholders(['search' => '']);

        $this->assertCount(3, $result->items());
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

    public function test_search_with_special_characters(): void
    {
        Stakeholder::factory()->create(['display_name' => "O'Brien"]);
        Stakeholder::factory()->create(['display_name' => 'Smith & Jones']);
        Stakeholder::factory()->create(['display_name' => 'Regular Name']);

        $result = $this->repository->getFilteredStakeholders(['search' => "O'Brien"]);

        $this->assertGreaterThanOrEqual(1, $result->total());
    }

    public function test_search_with_email_domain(): void
    {
        Stakeholder::factory()->create(['email' => 'user1@company.com']);
        Stakeholder::factory()->create(['email' => 'user2@company.com']);
        Stakeholder::factory()->create(['email' => 'user3@other.org']);

        $result = $this->repository->getFilteredStakeholders(['search' => 'company.com']);

        $this->assertCount(2, $result->items());
    }

    public function test_search_with_whitespace_in_name(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'Jane Doe']);

        $result = $this->repository->getFilteredStakeholders(['search' => 'John Smith']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('John Smith', $result->items()[0]->display_name);
    }

    public function test_search_matches_beginning_of_fields(): void
    {
        Stakeholder::factory()->create(['display_name' => 'Alexander']);
        Stakeholder::factory()->create(['display_name' => 'Alexandra']);
        Stakeholder::factory()->create(['display_name' => 'Alex']);

        $result = $this->repository->getFilteredStakeholders(['search' => 'Alex']);

        $this->assertCount(3, $result->items());
    }

    public function test_search_matches_middle_of_fields(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'Jane Johnson']);

        $result = $this->repository->getFilteredStakeholders(['search' => 'ohn']);

        $this->assertCount(2, $result->items());
    }

    public function test_search_matches_end_of_fields(): void
    {
        Stakeholder::factory()->create(['display_name' => 'Smith']);
        Stakeholder::factory()->create(['display_name' => 'Blacksmith']);

        $result = $this->repository->getFilteredStakeholders(['search' => 'smith']);

        $this->assertCount(2, $result->items());
    }

    public function test_pagination_works_with_search(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Stakeholder::factory()->create(['display_name' => "Tech User {$i}"]);
        }
        Stakeholder::factory()->create(['display_name' => 'Other User']);

        $result = $this->repository->getFilteredStakeholders([
            'search' => 'Tech',
            'per_page' => 5,
        ]);

        $this->assertCount(5, $result->items());
        $this->assertEquals(15, $result->total());
        $this->assertEquals(3, $result->lastPage());
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

    public function test_complex_filter_scenario(): void
    {
        // Create internal stakeholders with "Tech" in various fields
        Stakeholder::factory()->create([
            'type' => 'internal',
            'display_name' => 'Tech Lead',
            'email' => 'lead@internal.com',
        ]);
        Stakeholder::factory()->create([
            'type' => 'internal',
            'display_name' => 'Other Person',
            'legal_name' => 'Tech Corp',
        ]);

        // Create external stakeholder with "Tech"
        Stakeholder::factory()->create([
            'type' => 'external',
            'display_name' => 'Tech Consultant',
            'email' => 'consultant@external.com',
        ]);

        // Create internal without "Tech"
        Stakeholder::factory()->create([
            'type' => 'internal',
            'display_name' => 'Regular Employee',
        ]);

        $result = $this->repository->getFilteredStakeholders([
            'type' => 'internal',
            'search' => 'Tech',
        ]);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $item) {
            $this->assertEquals('internal', $item->type);
            $hasTech = str_contains(strtolower($item->display_name), 'tech') ||
                str_contains(strtolower($item->legal_name ?? ''), 'tech') ||
                str_contains(strtolower($item->email), 'tech');
            $this->assertTrue($hasTech);
        }
    }
}
