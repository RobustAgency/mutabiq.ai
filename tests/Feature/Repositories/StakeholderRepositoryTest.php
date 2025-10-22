<?php

namespace Tests\Feature\Repositories;

use App\Enums\Stakeholder\Type;
use App\Models\Stakeholder;
use App\Repositories\StakeholderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StakeholderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StakeholderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(StakeholderRepository::class);
    }

    public function test_create_a_stakeholder()
    {
        $data = [
            'type' => Type::PERSON->value,
            'display_name' => 'John Doe',
            'email' => 'john@example.com',
            'classification' => 'internal',
            'timezone' => 'UTC',
            'active' => true,
        ];

        $stakeholder = $this->repository->create($data);

        $this->assertEquals('John Doe', $stakeholder->display_name);
        $this->assertEquals('john@example.com', $stakeholder->email);
        $this->assertEquals(Type::PERSON->value, $stakeholder->type);
        $this->assertTrue($stakeholder->active);
        $this->assertDatabaseHas('stakeholders', ['email' => 'john@example.com']);
    }

    public function test_update_a_stakeholder()
    {
        $stakeholder = Stakeholder::factory()->create([
            'display_name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $updateData = [
            'display_name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $updatedStakeholder = $this->repository->update($stakeholder, $updateData);

        $this->assertEquals('Updated Name', $updatedStakeholder->display_name);
        $this->assertEquals('updated@example.com', $updatedStakeholder->email);
        $this->assertDatabaseHas('stakeholders', [
            'id' => $stakeholder->id,
            'display_name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_get_paginated_stakeholders()
    {
        Stakeholder::factory()->count(15)->create();

        $result = $this->repository->getFilteredStakeholders();

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
        $this->assertCount(10, $result->items());
    }

    public function test_get_paginated_stakeholders_with_custom_per_page()
    {
        Stakeholder::factory()->count(20)->create();

        $result = $this->repository->getFilteredStakeholders(['per_page' => 5]);

        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(20, $result->total());
        $this->assertCount(5, $result->items());
    }

    public function test_filter_stakeholders_by_type()
    {
        Stakeholder::factory()->count(5)->create(['type' => Type::PERSON->value]);
        Stakeholder::factory()->count(3)->create(['type' => Type::TEAM->value]);
        Stakeholder::factory()->count(2)->create(['type' => Type::VENDOR_ORG->value]);

        $result = $this->repository->getFilteredStakeholders(['type' => Type::PERSON->value]);

        $this->assertEquals(5, $result->total());
        foreach ($result->items() as $stakeholder) {
            $this->assertEquals(Type::PERSON->value, $stakeholder->type);
        }
    }

    public function test_returns_stakeholders_in_latest_order()
    {
        $firstStakeholder = Stakeholder::factory()->create(['created_at' => now()->subDays(2)]);
        $secondStakeholder = Stakeholder::factory()->create(['created_at' => now()->subDay()]);
        $thirdStakeholder = Stakeholder::factory()->create(['created_at' => now()]);

        $result = $this->repository->getFilteredStakeholders();

        $this->assertEquals($thirdStakeholder->id, $result->items()[0]->id);
        $this->assertEquals($secondStakeholder->id, $result->items()[1]->id);
        $this->assertEquals($firstStakeholder->id, $result->items()[2]->id);
    }

    public function test_create_stakeholder_with_all_fields()
    {
        $data = [
            'type' => Type::VENDOR_ORG->value,
            'display_name' => 'Acme Corp',
            'legal_name' => 'Acme Corporation Ltd',
            'org_unit' => 'Engineering',
            'email' => 'contact@acme.com',
            'phone' => '+1234567890',
            'vendor_id' => 'VND-1234',
            'role_tags' => ['admin', 'manager'],
            'timezone' => 'America/New_York',
            'classification' => 'external',
            'country' => 'US',
            'external_ref' => 'ext-123',
            'active' => true,
        ];

        $stakeholder = $this->repository->create($data);

        $this->assertEquals('Acme Corp', $stakeholder->display_name);
        $this->assertEquals('Acme Corporation Ltd', $stakeholder->legal_name);
        $this->assertEquals('Engineering', $stakeholder->org_unit);
        $this->assertEquals(['admin', 'manager'], $stakeholder->role_tags);
        $this->assertEquals('VND-1234', $stakeholder->vendor_id);
        $this->assertEquals('America/New_York', $stakeholder->timezone);
    }

    public function test_returns_empty_paginated_result_when_no_stakeholders_exist()
    {
        $result = $this->repository->getFilteredStakeholders();

        $this->assertEquals(0, $result->total());
        $this->assertCount(0, $result->items());
    }

    public function test_returns_empty_result_when_filtering_by_non_existent_type()
    {
        Stakeholder::factory()->count(5)->create(['type' => Type::PERSON->value]);

        $result = $this->repository->getFilteredStakeholders(['type' => Type::REGULATOR->value]);

        $this->assertEquals(0, $result->total());
        $this->assertCount(0, $result->items());
    }
}
