<?php

namespace Tests\Feature\Repositories;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Models\ConsentCoverage;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\Organization;
use App\Repositories\ConsentCoverageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentCoverageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ConsentCoverageRepository $repository;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ConsentCoverageRepository();
        $this->organization = Organization::factory()->create();
    }

    /**
     * Test get paginated coverages returns correct structure.
     */
    public function test_get_paginated_coverages_returns_paginator(): void
    {
        ConsentCoverage::factory()->count(5)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getPaginatedCoverages($this->organization->id);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
    }

    /**
     * Test get paginated coverages eager loads relationships.
     */
    public function test_get_paginated_coverages_eager_loads_relationships(): void
    {
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);
        $snapshot = DatasetSnapshot::factory()->for($dataset)->create(['organization_id' => $this->organization->id]);
        ConsentCoverage::factory()->for($dataset)->for($snapshot, 'snapshot')->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getPaginatedCoverages($this->organization->id);

        /** @var ConsentCoverage $coverage */
        $coverage = $result->items()[0];
        $this->assertTrue($coverage->relationLoaded('dataset'));
        $this->assertTrue($coverage->relationLoaded('snapshot'));
        $this->assertEquals($dataset->id, $coverage->dataset->id);
        $this->assertEquals($snapshot->id, $coverage->snapshot->id);
    }

    /**
     * Test get paginated coverages respects per page parameter.
     */
    public function test_get_paginated_coverages_respects_per_page(): void
    {
        ConsentCoverage::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getPaginatedCoverages($this->organization->id, 10);

        $this->assertEquals(10, $result->perPage());
        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    /**
     * Test get paginated coverages with default per page.
     */
    public function test_get_paginated_coverages_uses_default_per_page(): void
    {
        ConsentCoverage::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getPaginatedCoverages($this->organization->id);

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get paginated coverages orders by as_of desc.
     */
    public function test_get_paginated_coverages_ordered_by_as_of_desc(): void
    {
        $coverage1 = ConsentCoverage::factory()->create(['organization_id' => $this->organization->id, 'as_of' => now()->subDays(3)]);
        $coverage2 = ConsentCoverage::factory()->create(['organization_id' => $this->organization->id, 'as_of' => now()->subDays(1)]);
        $coverage3 = ConsentCoverage::factory()->create(['organization_id' => $this->organization->id, 'as_of' => now()->subDays(2)]);

        $result = $this->repository->getPaginatedCoverages($this->organization->id);

        $this->assertEquals($coverage2->id, $result->items()[0]->id);
        $this->assertEquals($coverage3->id, $result->items()[1]->id);
        $this->assertEquals($coverage1->id, $result->items()[2]->id);
    }

    /**
     * Test create coverage creates a new coverage.
     */
    public function test_create_coverage_creates_new_coverage(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create(['organization_id' => $organization->id]);
        $snapshot = DatasetSnapshot::factory()->for($dataset)->create(['organization_id' => $organization->id]);

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'snapshot_id' => $snapshot->id,
            'purpose' => ConsentPurpose::MARKETING->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'as_of' => now(),
            'subjects_total' => 10000,
            'subjects_with_valid_consent' => 8500,
            'coverage_pct' => 85.00,
            'evidence_ref' => 'EVD-TEST-001',
        ];

        $result = $this->repository->createCoverage($data);

        $this->assertInstanceOf(ConsentCoverage::class, $result);
        $this->assertEquals($data['dataset_id'], $result->dataset_id);
        $this->assertEquals($data['snapshot_id'], $result->snapshot_id);
        $this->assertEquals($data['purpose'], $result->purpose);
        $this->assertEquals($data['subjects_total'], $result->subjects_total);
        $this->assertEquals($data['subjects_with_valid_consent'], $result->subjects_with_valid_consent);
        $this->assertEquals($data['coverage_pct'], (float) $result->coverage_pct);
        $this->assertDatabaseHas('consent_coverages', [
            'dataset_id' => $dataset->id,
            'subjects_total' => 10000,
        ]);
    }

    /**
     * Test create coverage with minimal required data.
     */
    public function test_create_coverage_with_minimal_data(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create(['organization_id' => $organization->id]);

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'purpose' => ConsentPurpose::ANALYTICS->value,
            'jurisdiction' => Jurisdiction::US->value,
            'as_of' => now(),
            'subjects_total' => 5000,
            'subjects_with_valid_consent' => 5000,
            'coverage_pct' => 100.00,
            'evidence_ref' => 'EVD-FULL',
        ];

        $result = $this->repository->createCoverage($data);

        $this->assertInstanceOf(ConsentCoverage::class, $result);
        $this->assertNull($result->snapshot_id);
        $this->assertEquals(100.00, (float) $result->coverage_pct);
    }

    /**
     * Test create coverage automatically sets created_at if not provided.
     */
    public function test_create_coverage_sets_created_at_automatically(): void
    {
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);

        $data = [
            'organization_id' => $this->organization->id,
            'dataset_id' => $dataset->id,
            'purpose' => ConsentPurpose::MARKETING->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'as_of' => now(),
            'subjects_total' => 1000,
            'subjects_with_valid_consent' => 900,
            'coverage_pct' => 90.00,
            'evidence_ref' => 'EVD-AUTO',
        ];

        $result = $this->repository->createCoverage($data);

        $this->assertNotNull($result->created_at);
    }

    /**
     * Test update coverage updates existing coverage.
     */
    public function test_update_coverage_updates_existing_coverage(): void
    {
        $coverage = ConsentCoverage::factory()->create([
            'organization_id' => $this->organization->id,
            'subjects_total' => 1000,
            'subjects_with_valid_consent' => 800,
            'coverage_pct' => 80.00,
        ]);

        $updateData = [
            'subjects_total' => 1200,
            'subjects_with_valid_consent' => 1100,
            'coverage_pct' => 91.67,
        ];

        $result = $this->repository->updateCoverage($coverage, $updateData);

        $this->assertTrue($result);
        $coverage->refresh();
        $this->assertEquals(1200, $coverage->subjects_total);
        $this->assertEquals(1100, $coverage->subjects_with_valid_consent);
        $this->assertEquals(91.67, (float) $coverage->coverage_pct);
    }

    /**
     * Test update coverage can change purpose and jurisdiction.
     */
    public function test_update_coverage_can_change_purpose_and_jurisdiction(): void
    {
        $coverage = ConsentCoverage::factory()->create([
            'organization_id' => $this->organization->id,
            'purpose' => ConsentPurpose::MARKETING->value,
            'jurisdiction' => Jurisdiction::EU->value,
        ]);

        $this->repository->updateCoverage($coverage, [
            'purpose' => ConsentPurpose::TRAINING_AI->value,
            'jurisdiction' => Jurisdiction::US->value,
        ]);

        $coverage->refresh();
        $this->assertEquals(ConsentPurpose::TRAINING_AI->value, $coverage->purpose);
        $this->assertEquals(Jurisdiction::US->value, $coverage->jurisdiction);
    }

    /**
     * Test delete coverage deletes the coverage.
     */
    public function test_delete_coverage_deletes_coverage(): void
    {
        $coverage = ConsentCoverage::factory()->create(['organization_id' => $this->organization->id]);
        $coverageId = $coverage->id;

        $result = $this->repository->deleteCoverage($coverage);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('consent_coverages', ['id' => $coverageId]);
    }

    /**
     * Test delete coverage returns false on failure.
     */
    public function test_delete_coverage_returns_false_on_failure(): void
    {
        $coverage = ConsentCoverage::factory()->create(['organization_id' => $this->organization->id]);

        // Delete it first
        $coverage->delete();

        // Try to delete again - should return false
        $result = $this->repository->deleteCoverage($coverage);

        $this->assertFalse($result);
    }

    /**
     * Test coverage with different purposes.
     */
    public function test_repository_handles_all_purposes(): void
    {
        $purposes = [
            ConsentPurpose::MARKETING,
            ConsentPurpose::ANALYTICS,
            ConsentPurpose::PERSONALIZATION,
            ConsentPurpose::TRAINING_AI,
        ];

        foreach ($purposes as $purpose) {
            $coverage = ConsentCoverage::factory()->create([
                'organization_id' => $this->organization->id,
                'purpose' => $purpose->value,
            ]);
            $this->assertEquals($purpose->value, $coverage->purpose);
        }
    }

    /**
     * Test coverage with different jurisdictions.
     */
    public function test_repository_handles_all_jurisdictions(): void
    {
        $jurisdictions = [
            Jurisdiction::AE,
            Jurisdiction::EU,
            Jurisdiction::KSA,
            Jurisdiction::US,
            Jurisdiction::UK,
        ];

        foreach ($jurisdictions as $jurisdiction) {
            $coverage = ConsentCoverage::factory()->create([
                'organization_id' => $this->organization->id,
                'jurisdiction' => $jurisdiction->value,
            ]);
            $this->assertEquals($jurisdiction->value, $coverage->jurisdiction);
        }
    }

    /**
     * Test repository handles nullable snapshot_id.
     */
    public function test_repository_handles_nullable_snapshot_id(): void
    {
        $coverage = ConsentCoverage::factory()->create([
            'organization_id' => $this->organization->id,
            'snapshot_id' => null,
        ]);

        $this->assertNull($coverage->snapshot_id);
        $this->assertInstanceOf(ConsentCoverage::class, $coverage);
    }
}
