<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AiCommittee;
use App\Enums\AiCommittee\Type;
use App\Enums\AiCommittee\Cadence;
use App\Repositories\AiCommitteeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiCommitteeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AiCommitteeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(AiCommitteeRepository::class);
    }

    /**
     * Test getting filtered committees with default pagination
     */
    public function test_get_filtered_committees_with_default_pagination(): void
    {
        AiCommittee::factory(20)->create();

        $result = $this->repository->getFilteredCommittees([]);

        $this->assertEquals(15, $result->count());
        $this->assertEquals(20, $result->total());
    }

    /**
     * Test getting filtered committees with custom per_page
     */
    public function test_get_filtered_committees_with_custom_per_page(): void
    {
        AiCommittee::factory(25)->create();

        $result = $this->repository->getFilteredCommittees(['per_page' => 10]);

        $this->assertEquals(10, $result->count());
        $this->assertEquals(25, $result->total());
    }

    /**
     * Test filtering by type
     */
    public function test_filter_by_type(): void
    {
        AiCommittee::factory(5)->create(['type' => Type::GOVERNANCE->value]);
        AiCommittee::factory(3)->create(['type' => Type::ETHICS->value]);
        AiCommittee::factory(2)->create(['type' => Type::RISK->value]);

        $result = $this->repository->getFilteredCommittees([
            'type' => Type::GOVERNANCE->value,
        ]);

        $this->assertEquals(5, $result->total());
        $this->assertTrue($result->every(fn ($committee) => $committee->type === Type::GOVERNANCE->value));
    }

    /**
     * Test filtering by cadence
     */
    public function test_filter_by_cadence(): void
    {
        AiCommittee::factory(4)->create(['cadence' => Cadence::MONTHLY->value]);
        AiCommittee::factory(6)->create(['cadence' => Cadence::QUARTERLY->value]);

        $result = $this->repository->getFilteredCommittees([
            'cadence' => Cadence::MONTHLY->value,
        ]);

        $this->assertEquals(4, $result->total());
        $this->assertTrue($result->every(fn ($committee) => $committee->cadence === Cadence::MONTHLY->value));
    }

    /**
     * Test filtering by active true
     */
    public function test_filter_by_active_true(): void
    {
        AiCommittee::factory(8)->active()->create();
        AiCommittee::factory(5)->inactive()->create();

        $result = $this->repository->getFilteredCommittees([
            'active' => true,
        ]);

        $this->assertEquals(8, $result->total());
        $this->assertTrue($result->every(fn ($committee) => $committee->active === true));
    }

    /**
     * Test filtering by active false
     */
    public function test_filter_by_active_false(): void
    {
        AiCommittee::factory(7)->active()->create();
        AiCommittee::factory(6)->inactive()->create();

        $result = $this->repository->getFilteredCommittees([
            'active' => false,
        ]);

        $this->assertEquals(6, $result->total());
        $this->assertTrue($result->every(fn ($committee) => $committee->active === false));
    }

    /**
     * Test filtering by name with partial match
     */
    public function test_filter_by_name_with_partial_match(): void
    {
        AiCommittee::factory()->create(['name' => 'Governance Committee']);
        AiCommittee::factory()->create(['name' => 'Ethics Board']);
        AiCommittee::factory()->create(['name' => 'Risk Management Committee']);

        $result = $this->repository->getFilteredCommittees([
            'name' => 'Committee',
        ]);

        $this->assertEquals(2, $result->total());
    }

    /**
     * Test filtering by name is case-insensitive
     */
    public function test_filter_by_name_is_case_insensitive(): void
    {
        AiCommittee::factory()->create(['name' => 'Governance Committee']);
        AiCommittee::factory()->create(['name' => 'Ethics Board']);

        $result = $this->repository->getFilteredCommittees([
            'name' => 'governance',
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Governance Committee', $result->first()->name);
    }

    /**
     * Test filtering by name returns nothing when no match
     */
    public function test_filter_by_name_no_match(): void
    {
        AiCommittee::factory()->create(['name' => 'Governance Committee']);

        $result = $this->repository->getFilteredCommittees([
            'name' => 'NonExistent',
        ]);

        $this->assertEquals(0, $result->total());
    }

    /**
     * Test filtering by multiple filters combined
     */
    public function test_filter_by_multiple_filters(): void
    {
        AiCommittee::factory(10)->create([
            'type' => Type::GOVERNANCE->value,
            'cadence' => Cadence::MONTHLY->value,
            'active' => true,
        ]);

        AiCommittee::factory(5)->create([
            'type' => Type::GOVERNANCE->value,
            'cadence' => Cadence::QUARTERLY->value,
            'active' => true,
        ]);

        AiCommittee::factory(3)->create([
            'type' => Type::ETHICS->value,
            'cadence' => Cadence::MONTHLY->value,
            'active' => true,
        ]);

        $result = $this->repository->getFilteredCommittees([
            'type' => Type::GOVERNANCE->value,
            'cadence' => Cadence::MONTHLY->value,
            'active' => true,
        ]);

        $this->assertEquals(10, $result->total());
    }

    /**
     * Test filtering with no matching results
     */
    public function test_filter_with_no_matching_results(): void
    {
        AiCommittee::factory(5)->create(['type' => Type::GOVERNANCE->value]);

        $result = $this->repository->getFilteredCommittees([
            'type' => Type::ETHICS->value,
        ]);

        $this->assertEquals(0, $result->total());
    }

    /**
     * Test filter with empty filters array
     */
    public function test_filter_with_empty_filters_array(): void
    {
        AiCommittee::factory(10)->create();

        $result = $this->repository->getFilteredCommittees([]);

        $this->assertEquals(10, $result->total());
    }

    /**
     * Test create committee
     */
    public function test_create_committee(): void
    {
        $data = [
            'name' => 'AI Governance Committee',
            'type' => Type::GOVERNANCE->value,
            'charter' => 'Committee charter document',
            'cadence' => Cadence::MONTHLY->value,
            'owner_team' => 'Executive Team',
            'active' => true,
        ];

        $committee = $this->repository->createCommittee($data);

        $this->assertInstanceOf(AiCommittee::class, $committee);
        $this->assertEquals('AI Governance Committee', $committee->name);
        $this->assertEquals(Type::GOVERNANCE->value, $committee->type);
        $this->assertEquals(Cadence::MONTHLY->value, $committee->cadence);
        $this->assertTrue($committee->active);
        $this->assertDatabaseHas('ai_committees', ['name' => 'AI Governance Committee']);
    }

    /**
     * Test create committee with minimal data
     */
    public function test_create_committee_with_minimal_data(): void
    {
        $data = [
            'name' => 'Ethics Committee',
            'type' => Type::ETHICS->value,
            'charter' => 'Basic charter',
            'cadence' => Cadence::QUARTERLY->value,
            'owner_team' => 'Chief Ethics Officer',
            'active' => false,
        ];

        $committee = $this->repository->createCommittee($data);

        $this->assertNotNull($committee->id);
        $this->assertEquals('Ethics Committee', $committee->name);
        $this->assertFalse($committee->active);
    }

    /**
     * Test create committee returns instance with id
     */
    public function test_create_committee_returns_instance_with_id(): void
    {
        $data = [
            'name' => 'Risk Committee',
            'type' => Type::RISK->value,
            'charter' => 'Risk charter',
            'cadence' => Cadence::BIWEEKLY->value,
            'owner_team' => 'Risk Team',
            'active' => true,
        ];

        $committee = $this->repository->createCommittee($data);

        $this->assertNotNull($committee->id);
        $this->assertTrue($committee->id > 0);
    }

    /**
     * Test create multiple committees
     */
    public function test_create_multiple_committees(): void
    {
        $data1 = [
            'name' => 'Committee 1',
            'type' => Type::GOVERNANCE->value,
            'charter' => 'Charter 1',
            'cadence' => Cadence::MONTHLY->value,
            'owner_team' => 'Team 1',
            'active' => true,
        ];

        $data2 = [
            'name' => 'Committee 2',
            'type' => Type::ETHICS->value,
            'charter' => 'Charter 2',
            'cadence' => Cadence::QUARTERLY->value,
            'owner_team' => 'Team 2',
            'active' => false,
        ];

        $committee1 = $this->repository->createCommittee($data1);
        $committee2 = $this->repository->createCommittee($data2);

        $this->assertNotEquals($committee1->id, $committee2->id);
        $this->assertEquals(2, AiCommittee::count());
    }

    /**
     * Test update committee with full data
     */
    public function test_update_committee_full(): void
    {
        $committee = AiCommittee::factory()->create([
            'type' => Type::GOVERNANCE->value,
            'cadence' => Cadence::MONTHLY->value,
            'active' => true,
        ]);

        $updateData = [
            'name' => 'Updated Committee Name',
            'type' => Type::ETHICS->value,
            'cadence' => Cadence::QUARTERLY->value,
            'active' => false,
        ];

        $updated = $this->repository->updateCommittee($committee, $updateData);

        $this->assertEquals('Updated Committee Name', $updated->name);
        $this->assertEquals(Type::ETHICS->value, $updated->type);
        $this->assertEquals(Cadence::QUARTERLY->value, $updated->cadence);
        $this->assertFalse($updated->active);
    }

    /**
     * Test update committee with partial data
     */
    public function test_update_committee_partial(): void
    {
        $originalName = 'Original Name';
        $committee = AiCommittee::factory()->create([
            'name' => $originalName,
            'type' => Type::GOVERNANCE->value,
            'active' => true,
        ]);

        $updateData = [
            'active' => false,
        ];

        $updated = $this->repository->updateCommittee($committee, $updateData);

        $this->assertEquals($originalName, $updated->name);
        $this->assertEquals(Type::GOVERNANCE->value, $updated->type);
        $this->assertFalse($updated->active);
    }

    /**
     * Test update committee returns fresh instance
     */
    public function test_update_returns_fresh_instance(): void
    {
        $committee = AiCommittee::factory()->create([
            'name' => 'Original Name',
            'active' => true,
        ]);

        $updated = $this->repository->updateCommittee($committee, [
            'name' => 'Updated Name',
            'active' => false,
        ]);

        $this->assertNotSame($committee, $updated);
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertFalse($updated->active);
    }

    /**
     * Test update committee persists to database
     */
    public function test_update_committee_persists_to_database(): void
    {
        $committee = AiCommittee::factory()->create([
            'name' => 'Original',
            'active' => true,
        ]);

        $this->repository->updateCommittee($committee, [
            'name' => 'Updated',
            'active' => false,
        ]);

        $this->assertDatabaseHas('ai_committees', [
            'id' => $committee->id,
            'name' => 'Updated',
            'active' => false,
        ]);
    }

    /**
     * Test update preserves unmodified fields
     */
    public function test_update_preserves_unmodified_fields(): void
    {
        $originalCharter = 'Original Charter';
        $committee = AiCommittee::factory()->create([
            'name' => 'Original Name',
            'charter' => $originalCharter,
            'type' => Type::GOVERNANCE->value,
        ]);

        $updated = $this->repository->updateCommittee($committee, [
            'name' => 'Updated Name',
        ]);

        $this->assertEquals($originalCharter, $updated->charter);
        $this->assertEquals(Type::GOVERNANCE->value, $updated->type);
    }

    /**
     * Test update with all type enum values
     */
    public function test_update_with_all_type_enum_values(): void
    {
        $committee = AiCommittee::factory()->create(['type' => Type::GOVERNANCE->value]);

        foreach (Type::cases() as $type) {
            $updated = $this->repository->updateCommittee($committee, [
                'type' => $type->value,
            ]);

            $this->assertEquals($type->value, $updated->type);
        }
    }

    /**
     * Test update with all cadence enum values
     */
    public function test_update_with_all_cadence_enum_values(): void
    {
        $committee = AiCommittee::factory()->create(['cadence' => Cadence::MONTHLY->value]);

        foreach (Cadence::cases() as $cadence) {
            $updated = $this->repository->updateCommittee($committee, [
                'cadence' => $cadence->value,
            ]);

            $this->assertEquals($cadence->value, $updated->cadence);
        }
    }

    /**
     * Test delete committee
     */
    public function test_delete_committee(): void
    {
        $committee = AiCommittee::factory()->create();
        $id = $committee->id;

        $result = $this->repository->deleteCommittee($committee);

        $this->assertTrue($result);
        $this->assertNull(AiCommittee::find($id));
    }

    /**
     * Test delete returns true for successful deletion
     */
    public function test_delete_returns_true_on_success(): void
    {
        $committee = AiCommittee::factory()->create();

        $result = $this->repository->deleteCommittee($committee);

        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    /**
     * Test delete non-existent committee returns false
     */
    public function test_delete_returns_false_for_already_deleted(): void
    {
        $committee = AiCommittee::factory()->create();
        $committee->delete();

        $result = $this->repository->deleteCommittee($committee);

        $this->assertFalse($result);
    }

    /**
     * Test filter with combination of type and name
     */
    public function test_filter_by_type_and_name_combination(): void
    {
        AiCommittee::factory()->create([
            'name' => 'Governance Committee',
            'type' => Type::GOVERNANCE->value,
        ]);
        AiCommittee::factory()->create([
            'name' => 'Governance Board',
            'type' => Type::ETHICS->value,
        ]);
        AiCommittee::factory()->create([
            'name' => 'Ethics Committee',
            'type' => Type::ETHICS->value,
        ]);

        $result = $this->repository->getFilteredCommittees([
            'type' => Type::ETHICS->value,
            'name' => 'Committee',
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Ethics Committee', $result->first()->name);
    }

    /**
     * Test filter pagination data structure
     */
    public function test_filter_pagination_data_structure(): void
    {
        AiCommittee::factory(25)->create();

        $result = $this->repository->getFilteredCommittees(['per_page' => 10]);

        $this->assertNotNull($result->currentPage());
        $this->assertNotNull($result->perPage());
        $this->assertNotNull($result->total());
        $this->assertTrue($result->hasPages());
    }

    /**
     * Test filter returns paginator instance
     */
    public function test_filter_returns_paginator_instance(): void
    {
        AiCommittee::factory(5)->create();

        $result = $this->repository->getFilteredCommittees([]);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
    }
}
