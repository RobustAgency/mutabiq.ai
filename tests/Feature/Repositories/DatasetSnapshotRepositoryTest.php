<?php

namespace Tests\Feature\Repositories;

use App\Enums\DatasetSnapshot\ResidencyZone;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\Organization;
use App\Repositories\DatasetSnapshotRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatasetSnapshotRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DatasetSnapshotRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DatasetSnapshotRepository();
    }

    /**
     * Test get paginated snapshots returns correct structure.
     */
    public function test_get_paginated_snapshots_returns_paginator(): void
    {
        $organization = Organization::factory()->create();
        DatasetSnapshot::factory()->count(5)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDatasetSnapshots(['organization_id' => $organization->id]);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
    }

    /**
     * Test get paginated snapshots eager loads dataset relationship.
     */
    public function test_get_paginated_snapshots_eager_loads_dataset(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create();
        DatasetSnapshot::factory()->for($dataset)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDatasetSnapshots(['organization_id' => $organization->id]);

        /** @var DatasetSnapshot $snapshot */
        $snapshot = $result->items()[0];
        $this->assertTrue($snapshot->relationLoaded('dataset'));
        $this->assertEquals($dataset->id, $snapshot->dataset->id);
    }

    /**
     * Test get paginated snapshots respects per page parameter.
     */
    public function test_get_paginated_snapshots_respects_per_page(): void
    {
        $organization = Organization::factory()->create();
        DatasetSnapshot::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDatasetSnapshots(['organization_id' => $organization->id, 'per_page' => 10]);

        $this->assertEquals(10, $result->perPage());
        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    /**
     * Test get paginated snapshots with default per page.
     */
    public function test_get_paginated_snapshots_uses_default_per_page(): void
    {
        $organization = Organization::factory()->create();
        DatasetSnapshot::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDatasetSnapshots(['organization_id' => $organization->id]);

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get snapshots for dataset with Dataset model.
     */
    public function test_get_snapshots_for_dataset_with_model(): void
    {
        $dataset = Dataset::factory()->create();
        $otherDataset = Dataset::factory()->create();

        DatasetSnapshot::factory()->for($dataset)->count(3)->create(['organization_id' => $dataset->organization_id]);
        DatasetSnapshot::factory()->for($otherDataset)->count(2)->create(['organization_id' => $otherDataset->organization_id]);

        $result = $this->repository->getSnapshotsForDataset($dataset);

        $this->assertCount(3, $result);
        $result->each(function ($snapshot) use ($dataset) {
            $this->assertEquals($dataset->id, $snapshot->dataset_id);
        });
    }

    /**
     * Test get snapshots for dataset with dataset ID.
     */
    public function test_get_snapshots_for_dataset_with_id(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create();
        $otherDataset = Dataset::factory()->create();

        DatasetSnapshot::factory()->for($dataset)->count(3)->create(['organization_id' => $organization->id]);
        DatasetSnapshot::factory()->for($otherDataset)->count(2)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getSnapshotsForDataset($dataset->id);

        $this->assertCount(3, $result);
        $result->each(function ($snapshot) use ($dataset) {
            $this->assertEquals($dataset->id, $snapshot->dataset_id);
        });
    }

    /**
     * Test get snapshots for dataset returns ordered by created_at desc.
     */
    public function test_get_snapshots_for_dataset_ordered_by_created_at_desc(): void
    {
        $dataset = Dataset::factory()->create();

        $snapshot1 = DatasetSnapshot::factory()->for($dataset)->create(['created_at' => now()->subDays(3)]);
        $snapshot2 = DatasetSnapshot::factory()->for($dataset)->create(['created_at' => now()->subDays(1)]);
        $snapshot3 = DatasetSnapshot::factory()->for($dataset)->create(['created_at' => now()->subDays(2)]);

        $result = $this->repository->getSnapshotsForDataset($dataset);

        $this->assertEquals($snapshot2->id, $result->first()->id);
        $this->assertEquals($snapshot3->id, $result->get(1)->id);
        $this->assertEquals($snapshot1->id, $result->last()->id);
    }

    /**
     * Test get snapshots for dataset returns empty collection when no snapshots.
     */
    public function test_get_snapshots_for_dataset_returns_empty_collection_when_no_snapshots(): void
    {
        $dataset = Dataset::factory()->create();

        $result = $this->repository->getSnapshotsForDataset($dataset);

        $this->assertCount(0, $result);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
    }

    /**
     * Test get snapshot by ID returns snapshot.
     */
    public function test_get_snapshot_by_id_returns_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create();

        $result = $this->repository->getSnapshotById($snapshot->id);

        $this->assertNotNull($result);
        $this->assertEquals($snapshot->id, $result->id);
        $this->assertEquals($snapshot->version_tag, $result->version_tag);
    }

    /**
     * Test get snapshot by ID eager loads dataset.
     */
    public function test_get_snapshot_by_id_eager_loads_dataset(): void
    {
        $dataset = Dataset::factory()->create();
        $snapshot = DatasetSnapshot::factory()->for($dataset)->create();

        $result = $this->repository->getSnapshotById($snapshot->id);

        $this->assertTrue($result->relationLoaded('dataset'));
        $this->assertEquals($dataset->id, $result->dataset->id);
    }

    /**
     * Test get snapshot by ID returns null when not found.
     */
    public function test_get_snapshot_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getSnapshotById(999999);

        $this->assertNull($result);
    }

    /**
     * Test create snapshot creates a new snapshot.
     */
    public function test_create_snapshot_creates_new_snapshot(): void
    {
        $dataset = Dataset::factory()->create();
        $organization = Organization::factory()->create();

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'version_tag' => 'v1.0',
            'time_range_start' => now()->subMonths(3),
            'time_range_end' => now(),
            'row_count' => 50000,
            'quality_checksums' => hash('sha256', 'test'),
            'pii_element_count' => 10,
            'special_category_element_count' => 5,
            'masking_anonymization_method' => 'Tokenization',
            'privacy_transform_evidence_ref' => 'PTE-123456',
            'residency_zone' => ResidencyZone::EU,
            'storage_uri' => 'https://storage.example.com/snapshots/abc123',
        ];

        $result = $this->repository->createSnapshot($data);

        $this->assertInstanceOf(DatasetSnapshot::class, $result);
        $this->assertEquals($data['version_tag'], $result->version_tag);
        $this->assertEquals($data['dataset_id'], $result->dataset_id);
        $this->assertEquals($data['row_count'], $result->row_count);
        $this->assertEquals($data['residency_zone'], $result->residency_zone);
        $this->assertDatabaseHas('dataset_snapshots', [
            'version_tag' => 'v1.0',
        ]);
    }

    /**
     * Test create snapshot with minimal required data.
     */
    public function test_create_snapshot_with_minimal_data(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create(['organization_id' => $organization->id]);

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'version_tag' => 'v1.0',
            'residency_zone' => ResidencyZone::US,
            'storage_uri' => 'https://storage.example.com/snapshots/xyz789',
        ];

        $result = $this->repository->createSnapshot($data);

        $this->assertInstanceOf(DatasetSnapshot::class, $result);
        $this->assertNull($result->time_range_start);
        $this->assertNull($result->time_range_end);
        $this->assertNull($result->row_count);
    }

    /**
     * Test update snapshot updates existing snapshot.
     */
    public function test_update_snapshot_updates_existing_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'version_tag' => 'v1.0',
            'row_count' => 1000,
        ]);

        $updateData = [
            'version_tag' => 'v1.1',
            'row_count' => 1500,
            'quality_checksums' => hash('sha256', 'updated'),
        ];

        $result = $this->repository->updateSnapshot($snapshot, $updateData);

        $this->assertTrue($result);
        $snapshot->refresh();
        $this->assertEquals('v1.1', $snapshot->version_tag);
        $this->assertEquals(1500, $snapshot->row_count);
        $this->assertEquals($updateData['quality_checksums'], $snapshot->quality_checksums);
    }

    /**
     * Test delete snapshot deletes the snapshot.
     */
    public function test_delete_snapshot_deletes_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create();
        $snapshotId = $snapshot->id;

        $result = $this->repository->deleteSnapshot($snapshot);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('dataset_snapshots', ['id' => $snapshotId]);
    }

    /**
     * Test delete snapshot returns false on failure.
     */
    public function test_delete_snapshot_returns_false_on_failure(): void
    {
        $snapshot = DatasetSnapshot::factory()->create();

        // Delete it first
        $snapshot->delete();

        // Try to delete again - should return false
        $result = $this->repository->deleteSnapshot($snapshot);

        $this->assertFalse($result);
    }

    /**
     * Test repository handles nullable datetime fields.
     */
    public function test_repository_handles_nullable_datetime_fields(): void
    {
        $dataset = Dataset::factory()->create();

        $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
            'time_range_start' => null,
            'time_range_end' => null,
        ]);

        $result = $this->repository->getSnapshotById($snapshot->id);

        $this->assertNull($result->time_range_start);
        $this->assertNull($result->time_range_end);
    }

    /**
     * Test repository handles nullable integer fields.
     */
    public function test_repository_handles_nullable_integer_fields(): void
    {
        $dataset = Dataset::factory()->create();

        $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
            'row_count' => null,
            'pii_element_count' => null,
            'special_category_element_count' => null,
        ]);

        $result = $this->repository->getSnapshotById($snapshot->id);

        $this->assertNull($result->row_count);
        $this->assertNull($result->pii_element_count);
        $this->assertNull($result->special_category_element_count);
    }

    /**
     * Test repository handles nullable text fields.
     */
    public function test_repository_handles_nullable_text_fields(): void
    {
        $dataset = Dataset::factory()->create();

        $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
            'quality_checksums' => null,
            'masking_anonymization_method' => null,
            'privacy_transform_evidence_ref' => null,
        ]);

        $result = $this->repository->getSnapshotById($snapshot->id);

        $this->assertNull($result->quality_checksums);
        $this->assertNull($result->masking_anonymization_method);
        $this->assertNull($result->privacy_transform_evidence_ref);
    }
}
