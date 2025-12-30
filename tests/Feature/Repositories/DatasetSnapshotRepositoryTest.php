<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Dataset;
use App\Models\Organization;
use App\Models\DatasetSnapshot;
use App\Enums\DatasetSnapshot\Status;
use App\Enums\DatasetSnapshot\ApprovedBy;
use App\Enums\DatasetSnapshot\FileFormat;
use App\Enums\DatasetSnapshot\Compression;
use App\Enums\DatasetSnapshot\StorageTier;
use App\Enums\DatasetSnapshot\MaskingMethod;
use App\Enums\DatasetSnapshot\ResidencyZone;
use App\Enums\DatasetSnapshot\EncryptionStatus;
use App\Repositories\DatasetSnapshotRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatasetSnapshotRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DatasetSnapshotRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DatasetSnapshotRepository;
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
     * Test create snapshot creates a new snapshot with all required fields.
     */
    public function test_create_snapshot_creates_new_snapshot(): void
    {
        $dataset = Dataset::factory()->create();
        $organization = Organization::factory()->create();

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'version_tag' => 'v1.0',
            'supersedes_snapshot_id' => null,
            'description' => 'Test dataset snapshot',
            'time_range_start' => now()->subMonths(3),
            'time_range_end' => now(),
            'row_count' => 50000,
            'file_count' => 120,
            'total_size' => 5242880,
            'size_unit' => 'MB',
            'file_format' => FileFormat::PARQUET->value,
            'pii_element_count' => 10,
            'consent_coverage_at_creation' => 95,
            'residency_zone' => ResidencyZone::EU->value,
            'storage_uri' => 'https://storage.example.com/snapshots/abc123',
            'storage_tier' => StorageTier::HOT->value,
            'compression' => Compression::GZIP->value,
            'encryption_status' => EncryptionStatus::ENCRYPTED_AT_REST->value,
            'masking_method_applied' => MaskingMethod::TOKENIZATION->value,
            'quality_checksums' => hash('sha256', 'test'),
            'created_by_system' => false,
            'approved_by' => ApprovedBy::PRIVACY_OFFICE->value,
            'expiration_date' => now()->addYears(1),
            'status' => Status::ACTIVE->value,
        ];

        $result = $this->repository->createSnapshot($data);

        $this->assertInstanceOf(DatasetSnapshot::class, $result);
        $this->assertEquals($data['version_tag'], $result->version_tag);
        $this->assertEquals($data['dataset_id'], $result->dataset_id);
        $this->assertEquals($data['row_count'], $result->row_count);
        $this->assertEquals($data['file_count'], $result->file_count);
        $this->assertEquals($data['total_size'], $result->total_size);
        $this->assertEquals($data['file_format'], $result->file_format);
        $this->assertEquals($data['encryption_status'], $result->encryption_status);
        $this->assertEquals($data['status'], $result->status);
        $this->assertDatabaseHas('dataset_snapshots', [
            'version_tag' => 'v1.0',
            'file_format' => FileFormat::PARQUET->value,
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
            'time_range_start' => now()->subMonths(1),
            'time_range_end' => now(),
            'row_count' => 1000,
            'residency_zone' => ResidencyZone::US->value,
            'storage_uri' => 'https://storage.example.com/snapshots/xyz789',
            'encryption_status' => EncryptionStatus::ENCRYPTED_AT_REST->value,
            'status' => Status::ACTIVE->value,
            'file_format' => FileFormat::CSV->value,
        ];

        $result = $this->repository->createSnapshot($data);

        $this->assertInstanceOf(DatasetSnapshot::class, $result);
        $this->assertEquals($data['version_tag'], $result->version_tag);
        $this->assertEquals($data['dataset_id'], $result->dataset_id);
        $this->assertNull($result->file_count);
        $this->assertNull($result->total_size);
        $this->assertNull($result->masking_method_applied);
    }

    /**
     * Test update snapshot updates existing snapshot with new fields.
     */
    public function test_update_snapshot_updates_existing_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'version_tag' => 'v1.0',
            'row_count' => 1000,
            'status' => Status::ACTIVE->value,
        ]);

        $updateData = [
            'version_tag' => 'v1.1',
            'row_count' => 1500,
            'file_count' => 150,
            'total_size' => 2097152,
            'quality_checksums' => hash('sha256', 'updated'),
            'compression' => Compression::SNAPPY->value,
            'masking_method_applied' => MaskingMethod::HASHING->value,
            'status' => Status::DEPRECATED->value,
        ];

        $result = $this->repository->updateSnapshot($snapshot, $updateData);

        $this->assertTrue($result);
        $snapshot->refresh();
        $this->assertEquals('v1.1', $snapshot->version_tag);
        $this->assertEquals(1500, $snapshot->row_count);
        $this->assertEquals(150, $snapshot->file_count);
        $this->assertEquals($updateData['quality_checksums'], $snapshot->quality_checksums);
        $this->assertEquals(Compression::SNAPPY->value, $snapshot->compression);
        $this->assertEquals(Status::DEPRECATED->value, $snapshot->status);
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
            'expiration_date' => null,
        ]);

        $result = $this->repository->getSnapshotById($snapshot->id);

        $this->assertNull($result->time_range_start);
        $this->assertNull($result->time_range_end);
        $this->assertNull($result->expiration_date);
    }

    /**
     * Test repository handles nullable integer fields.
     */
    public function test_repository_handles_nullable_integer_fields(): void
    {
        $dataset = Dataset::factory()->create();

        $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
            'row_count' => null,
            'file_count' => null,
            'pii_element_count' => null,
            'total_size' => null,
            'consent_coverage_at_creation' => null,
        ]);

        $result = $this->repository->getSnapshotById($snapshot->id);

        $this->assertNull($result->row_count);
        $this->assertNull($result->file_count);
        $this->assertNull($result->pii_element_count);
        $this->assertNull($result->total_size);
        $this->assertNull($result->consent_coverage_at_creation);
    }

    /**
     * Test repository handles nullable text and enum fields.
     */
    public function test_repository_handles_nullable_text_and_enum_fields(): void
    {
        $dataset = Dataset::factory()->create();

        $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
            'quality_checksums' => null,
            'masking_method_applied' => null,
            'compression' => null,
            'storage_tier' => null,
            'approved_by' => null,
            'description' => null,
            'size_unit' => null,
            'supersedes_snapshot_id' => null,
        ]);

        $result = $this->repository->getSnapshotById($snapshot->id);

        $this->assertNull($result->quality_checksums);
        $this->assertNull($result->masking_method_applied);
        $this->assertNull($result->compression);
        $this->assertNull($result->storage_tier);
        $this->assertNull($result->approved_by);
        $this->assertNull($result->description);
        $this->assertNull($result->size_unit);
        $this->assertNull($result->supersedes_snapshot_id);
    }

    /**
     * Test repository handles all file format enums.
     */
    public function test_repository_handles_all_file_format_enums(): void
    {
        $dataset = Dataset::factory()->create();

        foreach (FileFormat::cases() as $format) {
            $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
                'file_format' => $format->value,
            ]);

            $result = $this->repository->getSnapshotById($snapshot->id);
            $this->assertEquals($format->value, $result->file_format);
        }
    }

    /**
     * Test repository handles all encryption status enums.
     */
    public function test_repository_handles_all_encryption_status_enums(): void
    {
        $dataset = Dataset::factory()->create();

        foreach (EncryptionStatus::cases() as $status) {
            $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
                'encryption_status' => $status->value,
            ]);

            $result = $this->repository->getSnapshotById($snapshot->id);
            $this->assertEquals($status->value, $result->encryption_status);
        }
    }

    /**
     * Test repository handles all residency zone enums.
     */
    public function test_repository_handles_all_residency_zone_enums(): void
    {
        $dataset = Dataset::factory()->create();

        foreach (ResidencyZone::cases() as $zone) {
            $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
                'residency_zone' => $zone->value,
            ]);

            $result = $this->repository->getSnapshotById($snapshot->id);
            $this->assertEquals($zone->value, $result->residency_zone);
        }
    }

    /**
     * Test repository handles all status enums.
     */
    public function test_repository_handles_all_status_enums(): void
    {
        $dataset = Dataset::factory()->create();

        foreach (Status::cases() as $status) {
            $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
                'status' => $status->value,
            ]);

            $result = $this->repository->getSnapshotById($snapshot->id);
            $this->assertEquals($status->value, $result->status);
        }
    }

    /**
     * Test repository handles compression enums.
     */
    public function test_repository_handles_compression_enums(): void
    {
        $dataset = Dataset::factory()->create();

        foreach (Compression::cases() as $compression) {
            $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
                'compression' => $compression->value,
            ]);

            $result = $this->repository->getSnapshotById($snapshot->id);
            $this->assertEquals($compression->value, $result->compression);
        }
    }

    /**
     * Test repository handles storage tier enums.
     */
    public function test_repository_handles_storage_tier_enums(): void
    {
        $dataset = Dataset::factory()->create();

        foreach (StorageTier::cases() as $tier) {
            $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
                'storage_tier' => $tier->value,
            ]);

            $result = $this->repository->getSnapshotById($snapshot->id);
            $this->assertEquals($tier->value, $result->storage_tier);
        }
    }

    /**
     * Test repository handles masking method enums.
     */
    public function test_repository_handles_masking_method_enums(): void
    {
        $dataset = Dataset::factory()->create();

        foreach (MaskingMethod::cases() as $method) {
            $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
                'masking_method_applied' => $method->value,
            ]);

            $result = $this->repository->getSnapshotById($snapshot->id);
            $this->assertEquals($method->value, $result->masking_method_applied);
        }
    }

    /**
     * Test repository handles approved by enums.
     */
    public function test_repository_handles_approved_by_enums(): void
    {
        $dataset = Dataset::factory()->create();

        foreach (ApprovedBy::cases() as $approver) {
            $snapshot = DatasetSnapshot::factory()->for($dataset)->create([
                'approved_by' => $approver->value,
            ]);

            $result = $this->repository->getSnapshotById($snapshot->id);
            $this->assertEquals($approver->value, $result->approved_by);
        }
    }

    /**
     * Test repository handles boolean fields.
     */
    public function test_repository_handles_boolean_fields(): void
    {
        $dataset = Dataset::factory()->create();

        $snapshotTrue = DatasetSnapshot::factory()->for($dataset)->create([
            'created_by_system' => true,
        ]);

        $snapshotFalse = DatasetSnapshot::factory()->for($dataset)->create([
            'created_by_system' => false,
        ]);

        $resultTrue = $this->repository->getSnapshotById($snapshotTrue->id);
        $resultFalse = $this->repository->getSnapshotById($snapshotFalse->id);

        $this->assertTrue($resultTrue->created_by_system);
        $this->assertFalse($resultFalse->created_by_system);
    }

    /**
     * Test repository handles snapshot with superseded relationship.
     */
    public function test_repository_handles_snapshot_superseding(): void
    {
        $dataset = Dataset::factory()->create();
        $previousSnapshot = DatasetSnapshot::factory()->for($dataset)->create([
            'version_tag' => 'v1.0',
            'status' => Status::ACTIVE->value,
        ]);

        $newSnapshot = DatasetSnapshot::factory()->for($dataset)->create([
            'version_tag' => 'v2.0',
            'supersedes_snapshot_id' => $previousSnapshot->id,
            'status' => Status::ACTIVE->value,
        ]);

        $result = $this->repository->getSnapshotById($newSnapshot->id);

        $this->assertEquals($previousSnapshot->id, $result->supersedes_snapshot_id);
        $this->assertDatabaseHas('dataset_snapshots', [
            'id' => $newSnapshot->id,
            'supersedes_snapshot_id' => $previousSnapshot->id,
        ]);
    }
}
