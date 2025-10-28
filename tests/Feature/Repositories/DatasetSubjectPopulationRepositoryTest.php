<?php

namespace Tests\Feature\Repositories;

use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\DatasetSubjectPopulation;
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
        DatasetSubjectPopulation::factory()->count(20)->create();

        $result = $this->repository->getPaginatedPopulations(10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /**
     * Test get paginated populations uses default per page value.
     */
    public function test_get_paginated_populations_uses_default_per_page(): void
    {
        DatasetSubjectPopulation::factory()->count(20)->create();

        $result = $this->repository->getPaginatedPopulations();

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get paginated populations orders by as_of descending.
     */
    public function test_get_paginated_populations_orders_by_as_of_desc(): void
    {
        $oldPopulation = DatasetSubjectPopulation::factory()->create([
            'as_of' => now()->subDays(10),
        ]);
        $newPopulation = DatasetSubjectPopulation::factory()->create([
            'as_of' => now(),
        ]);

        $result = $this->repository->getPaginatedPopulations();

        $this->assertEquals($newPopulation->id, $result->items()[0]->id);
        $this->assertEquals($oldPopulation->id, $result->items()[1]->id);
    }

    /**
     * Test get paginated populations eager loads relationships.
     */
    public function test_get_paginated_populations_eager_loads_relationships(): void
    {
        DatasetSubjectPopulation::factory()->create();

        $result = $this->repository->getPaginatedPopulations();

        $this->assertTrue($result->items()[0]->relationLoaded('dataset'));
        $this->assertTrue($result->items()[0]->relationLoaded('snapshot'));
    }

    /**
     * Test get paginated populations returns empty when no records.
     */
    public function test_get_paginated_populations_returns_empty_when_no_records(): void
    {
        $result = $this->repository->getPaginatedPopulations();

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test create population creates new record with all fields.
     */
    public function test_create_population_creates_new_record_with_all_fields(): void
    {
        $dataset = Dataset::factory()->create();
        $snapshot = DatasetSnapshot::factory()->for($dataset)->create();

        $data = [
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
        $dataset = Dataset::factory()->create();

        $data = [
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
        $dataset = Dataset::factory()->create();

        $data = [
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

    /**
     * Test create population with all subject realms.
     */
    public function test_create_population_with_all_subject_realms(): void
    {
        $dataset = Dataset::factory()->create();

        $realms = [
            SubjectRealm::CUSTOMER,
            SubjectRealm::PROSPECT,
            SubjectRealm::EMPLOYEE,
            SubjectRealm::VENDOR,
            SubjectRealm::OTHER,
        ];

        foreach ($realms as $realm) {
            $data = [
                'dataset_id' => $dataset->id,
                'subject_realm' => $realm->value,
                'jurisdiction' => Jurisdiction::EU->value,
                'subjects_total' => 1000,
                'as_of' => now(),
            ];

            $population = $this->repository->createPopulation($data);

            $this->assertEquals($realm->value, $population->subject_realm);
        }
    }

    /**
     * Test create population with all jurisdictions.
     */
    public function test_create_population_with_all_jurisdictions(): void
    {
        $dataset = Dataset::factory()->create();

        $jurisdictions = [
            Jurisdiction::AE,
            Jurisdiction::EU,
            Jurisdiction::KSA,
            Jurisdiction::US,
            Jurisdiction::UK,
            Jurisdiction::QA,
            Jurisdiction::JO,
            Jurisdiction::MA,
            Jurisdiction::BH,
            Jurisdiction::OTHER,
        ];

        foreach ($jurisdictions as $jurisdiction) {
            $data = [
                'dataset_id' => $dataset->id,
                'subject_realm' => SubjectRealm::CUSTOMER->value,
                'jurisdiction' => $jurisdiction->value,
                'subjects_total' => 1000,
                'as_of' => now(),
            ];

            $population = $this->repository->createPopulation($data);

            $this->assertEquals($jurisdiction->value, $population->jurisdiction);
        }
    }

    /**
     * Test create population with zero subjects.
     */
    public function test_create_population_with_zero_subjects(): void
    {
        $dataset = Dataset::factory()->create();

        $data = [
            'dataset_id' => $dataset->id,
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'subjects_total' => 0,
            'as_of' => now(),
        ];

        $population = $this->repository->createPopulation($data);

        $this->assertEquals(0, $population->subjects_total);
    }

    /**
     * Test create population with large subject numbers.
     */
    public function test_create_population_with_large_numbers(): void
    {
        $dataset = Dataset::factory()->create();

        $data = [
            'dataset_id' => $dataset->id,
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'subjects_total' => 50000000,
            'as_of' => now(),
        ];

        $population = $this->repository->createPopulation($data);

        $this->assertEquals(50000000, $population->subjects_total);
    }

    /**
     * Test create population with past as_of date.
     */
    public function test_create_population_with_past_as_of_date(): void
    {
        $dataset = Dataset::factory()->create();
        $pastDate = now()->subMonths(6);

        $data = [
            'dataset_id' => $dataset->id,
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'subjects_total' => 1000,
            'as_of' => $pastDate,
        ];

        $population = $this->repository->createPopulation($data);

        $this->assertEquals($pastDate->timestamp, $population->as_of->timestamp);
    }
}
