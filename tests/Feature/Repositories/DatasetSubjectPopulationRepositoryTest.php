<?php

namespace Tests\Feature\Repositories;

use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\DatasetSubjectPopulation;
use App\Models\Organization;
use App\Repositories\DatasetSubjectPopulationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatasetSubjectPopulationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DatasetSubjectPopulationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DatasetSubjectPopulationRepository();
    }

    /**
     * Test get paginated populations returns paginated results.
     */
    public function test_get_paginated_populations_returns_paginated_results(): void
    {
        $organization = Organization::factory()->create();
        DatasetSubjectPopulation::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDatasetSubjectPopulations(['organization_id' => $organization->id, 'per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /**
     * Test get paginated populations uses default per page value.
     */
    public function test_get_paginated_populations_uses_default_per_page(): void
    {
        $organization = Organization::factory()->create();
        DatasetSubjectPopulation::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDatasetSubjectPopulations(['organization_id' => $organization->id]);

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get paginated populations orders by as_of descending.
     */
    public function test_get_paginated_populations_orders_by_as_of_desc(): void
    {
        $organization = Organization::factory()->create();
        $oldPopulation = DatasetSubjectPopulation::factory()->create([
            'as_of' => now()->subDays(10),
            'organization_id' => $organization->id,
        ]);
        $newPopulation = DatasetSubjectPopulation::factory()->create([
            'as_of' => now(),
            'organization_id' => $organization->id,
        ]);

        $result = $this->repository->getFilteredDatasetSubjectPopulations(['organization_id' => $organization->id]);

        $this->assertEquals($newPopulation->id, $result->items()[0]->id);
        $this->assertEquals($oldPopulation->id, $result->items()[1]->id);
    }

    /**
     * Test get paginated populations eager loads relationships.
     */
    public function test_get_paginated_populations_eager_loads_relationships(): void
    {
        $organization = Organization::factory()->create();
        DatasetSubjectPopulation::factory()->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDatasetSubjectPopulations(['organization_id' => $organization->id]);

        $this->assertTrue($result->items()[0]->relationLoaded('dataset'));
        $this->assertTrue($result->items()[0]->relationLoaded('snapshot'));
    }

    /**
     * Test create population creates new record with all fields.
     */
    public function test_create_population_creates_new_record_with_all_fields(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create();
        $snapshot = DatasetSnapshot::factory()->for($dataset)->create();

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'snapshot_id' => $snapshot->id,
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'subjects_total' => 10000,
            'as_of' => now(),
        ];

        $population = $this->repository->createPopulation($data);

        $this->assertInstanceOf(DatasetSubjectPopulation::class, $population);
        $this->assertEquals($data['dataset_id'], $population->dataset_id);
        $this->assertEquals($data['snapshot_id'], $population->snapshot_id);
        $this->assertEquals($data['subject_realm'], $population->subject_realm);
        $this->assertEquals($data['jurisdiction'], $population->jurisdiction);
        $this->assertEquals($data['subjects_total'], $population->subjects_total);
        $this->assertNotNull($population->id);
    }

    /**
     * Test create population without snapshot_id.
     */
    public function test_create_population_without_snapshot_id(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create();

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'subject_realm' => SubjectRealm::EMPLOYEE->value,
            'jurisdiction' => Jurisdiction::US->value,
            'subjects_total' => 5000,
            'as_of' => now(),
        ];

        $population = $this->repository->createPopulation($data);

        $this->assertNull($population->snapshot_id);
        $this->assertEquals($data['dataset_id'], $population->dataset_id);
    }

    /**
     * Test create population auto-sets created_at if not provided.
     */
    public function test_create_population_auto_sets_created_at(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create();

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'subjects_total' => 1000,
            'as_of' => now(),
        ];

        $population = $this->repository->createPopulation($data);

        $this->assertNotNull($population->created_at);
    }

    /**
     * Test update population updates fields.
     */
    public function test_update_population_updates_fields(): void
    {
        $population = DatasetSubjectPopulation::factory()->create([
            'subjects_total' => 1000,
            'subject_realm' => SubjectRealm::CUSTOMER->value,
        ]);

        $updateData = [
            'subjects_total' => 2000,
            'subject_realm' => SubjectRealm::EMPLOYEE->value,
        ];

        $updated = $this->repository->updatePopulation($population, $updateData);

        $this->assertEquals(2000, $updated->subjects_total);
        $this->assertEquals(SubjectRealm::EMPLOYEE->value, $updated->subject_realm);
    }

    /**
     * Test update population returns fresh instance.
     */
    public function test_update_population_returns_fresh_instance(): void
    {
        $population = DatasetSubjectPopulation::factory()->create([
            'subjects_total' => 1000,
        ]);

        $updateData = [
            'subjects_total' => 2000,
        ];

        $updated = $this->repository->updatePopulation($population, $updateData);

        $this->assertNotSame($population, $updated);
        $this->assertEquals(2000, $updated->subjects_total);
    }

    /**
     * Test update population partial update.
     */
    public function test_update_population_partial_update(): void
    {
        $population = DatasetSubjectPopulation::factory()->create([
            'subjects_total' => 1000,
            'jurisdiction' => Jurisdiction::EU->value,
        ]);

        $updateData = [
            'subjects_total' => 1500,
        ];

        $updated = $this->repository->updatePopulation($population, $updateData);

        $this->assertEquals(1500, $updated->subjects_total);
        $this->assertEquals(Jurisdiction::EU->value, $updated->jurisdiction);
    }

    /**
     * Test delete population removes record.
     */
    public function test_delete_population_removes_record(): void
    {
        $population = DatasetSubjectPopulation::factory()->create();

        $result = $this->repository->deletePopulation($population);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('dataset_subject_populations', [
            'id' => $population->id,
        ]);
    }
}
