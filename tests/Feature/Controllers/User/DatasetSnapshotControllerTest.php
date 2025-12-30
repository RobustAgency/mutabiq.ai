<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
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
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatasetSnapshotControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    private function validPayload(array $overrides = []): array
    {
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);

        return array_merge([
            'dataset_id' => $dataset->id,
            'version_tag' => 'v1.0',
            'description' => 'Test dataset snapshot',
            'time_range_start' => '2024-01-01',
            'time_range_end' => '2024-12-31',
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
            'expiration_date' => now()->addYears(1)->toDateString(),
            'status' => Status::ACTIVE->value,
        ], $overrides);
    }

    // ==================== Index Tests ====================

    /**
     * Test user can get paginated dataset snapshots.
     */
    public function test_user_can_get_paginated_dataset_snapshots(): void
    {
        DatasetSnapshot::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/dataset-snapshots');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'dataset_id',
                            'version_tag',
                            'description',
                            'time_range_start',
                            'time_range_end',
                            'row_count',
                            'file_count',
                            'total_size',
                            'file_format',
                            'encryption_status',
                            'status',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ])
            ->assertJson(['error' => false]);
    }

    /**
     * Test user can get paginated dataset snapshots with custom per_page.
     */
    public function test_user_can_get_paginated_dataset_snapshots_with_custom_per_page(): void
    {
        DatasetSnapshot::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/dataset-snapshots?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    // ==================== Store Tests ====================

    /**
     * Test user can create a dataset snapshot with all fields.
     */
    public function test_user_can_create_dataset_snapshot_with_all_fields(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'dataset_id',
                    'version_tag',
                    'file_format',
                    'residency_zone',
                    'encryption_status',
                    'storage_uri',
                    'status',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Dataset snapshot created successfully',
            ]);

        $this->assertDatabaseHas('dataset_snapshots', [
            'dataset_id' => $payload['dataset_id'],
            'version_tag' => $payload['version_tag'],
            'file_format' => $payload['file_format'],
            'encryption_status' => $payload['encryption_status'],
            'status' => $payload['status'],
        ]);
    }

    /**
     * Test user can create a dataset snapshot with minimal required fields.
     */
    public function test_user_can_create_dataset_snapshot_with_minimal_fields(): void
    {
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);

        $payload = [
            'dataset_id' => $dataset->id,
            'version_tag' => 'v1.0',
            'time_range_start' => '2024-01-01',
            'time_range_end' => '2024-12-31',
            'row_count' => 1000,
            'file_format' => FileFormat::CSV->value,
            'residency_zone' => ResidencyZone::US->value,
            'storage_uri' => 'https://storage.example.com/snapshots/xyz789',
            'encryption_status' => EncryptionStatus::ENCRYPTED_AT_REST->value,
            'status' => Status::ACTIVE->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset snapshot created successfully',
            ]);

        $this->assertDatabaseHas('dataset_snapshots', [
            'dataset_id' => $payload['dataset_id'],
            'version_tag' => $payload['version_tag'],
        ]);
    }

    // ==================== Validation Tests ====================

    /**
     * Test dataset_id is required.
     */
    public function test_dataset_id_is_required(): void
    {
        $payload = $this->validPayload(['dataset_id' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_id']);
    }

    /**
     * Test dataset_id must exist in datasets table.
     */
    public function test_dataset_id_must_exist(): void
    {
        $payload = $this->validPayload(['dataset_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_id']);
    }

    /**
     * Test version_tag is required.
     */
    public function test_version_tag_is_required(): void
    {
        $payload = $this->validPayload(['version_tag' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['version_tag']);
    }

    /**
     * Test version_tag max length is 50.
     */
    public function test_version_tag_max_length_is_50(): void
    {
        $payload = $this->validPayload(['version_tag' => str_repeat('a', 51)]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['version_tag']);
    }

    /**
     * Test time_range_start is required.
     */
    public function test_time_range_start_is_required(): void
    {
        $payload = $this->validPayload(['time_range_start' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['time_range_start']);
    }

    /**
     * Test time_range_end is required.
     */
    public function test_time_range_end_is_required(): void
    {
        $payload = $this->validPayload(['time_range_end' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['time_range_end']);
    }

    /**
     * Test time_range_end must be after or equal to time_range_start.
     */
    public function test_time_range_end_must_be_after_or_equal_to_start(): void
    {
        $payload = $this->validPayload([
            'time_range_start' => '2024-12-31',
            'time_range_end' => '2024-01-01',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['time_range_end']);
    }

    /**
     * Test row_count is required.
     */
    public function test_row_count_is_required(): void
    {
        $payload = $this->validPayload(['row_count' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['row_count']);
    }

    /**
     * Test row_count must be non-negative.
     */
    public function test_row_count_must_be_non_negative(): void
    {
        $payload = $this->validPayload(['row_count' => -1]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['row_count']);
    }

    /**
     * Test file_count must be non-negative.
     */
    public function test_file_count_must_be_non_negative(): void
    {
        $payload = $this->validPayload(['file_count' => -1]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_count']);
    }

    /**
     * Test total_size must be non-negative.
     */
    public function test_total_size_must_be_non_negative(): void
    {
        $payload = $this->validPayload(['total_size' => -1]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_size']);
    }

    /**
     * Test file_format is required.
     */
    public function test_file_format_is_required(): void
    {
        $payload = $this->validPayload(['file_format' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_format']);
    }

    /**
     * Test file_format must be valid enum value.
     */
    public function test_file_format_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['file_format' => 'INVALID_FORMAT']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_format']);
    }

    /**
     * Test pii_element_count must be non-negative.
     */
    public function test_pii_element_count_must_be_non_negative(): void
    {
        $payload = $this->validPayload(['pii_element_count' => -1]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pii_element_count']);
    }

    /**
     * Test consent_coverage_at_creation must be between 0 and 100.
     */
    public function test_consent_coverage_at_creation_max_is_100(): void
    {
        $payload = $this->validPayload(['consent_coverage_at_creation' => 101]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['consent_coverage_at_creation']);
    }

    /**
     * Test residency_zone is required.
     */
    public function test_residency_zone_is_required(): void
    {
        $payload = $this->validPayload(['residency_zone' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['residency_zone']);
    }

    /**
     * Test residency_zone must be valid enum value.
     */
    public function test_residency_zone_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['residency_zone' => 'INVALID']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['residency_zone']);
    }

    /**
     * Test storage_uri is required.
     */
    public function test_storage_uri_is_required(): void
    {
        $payload = $this->validPayload(['storage_uri' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['storage_uri']);
    }

    /**
     * Test storage_uri max length is 500.
     */
    public function test_storage_uri_max_length_is_500(): void
    {
        $payload = $this->validPayload(['storage_uri' => str_repeat('a', 501)]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['storage_uri']);
    }

    /**
     * Test encryption_status is required.
     */
    public function test_encryption_status_is_required(): void
    {
        $payload = $this->validPayload(['encryption_status' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['encryption_status']);
    }

    /**
     * Test encryption_status must be valid enum value.
     */
    public function test_encryption_status_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['encryption_status' => 'INVALID_STATUS']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['encryption_status']);
    }

    /**
     * Test status is required.
     */
    public function test_status_is_required(): void
    {
        $payload = $this->validPayload(['status' => null]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test status must be valid enum value.
     */
    public function test_status_must_be_valid_enum(): void
    {
        $payload = $this->validPayload(['status' => 'INVALID_STATUS']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test quality_checksums max length is 255.
     */
    public function test_quality_checksums_max_length_is_255(): void
    {
        $payload = $this->validPayload(['quality_checksums' => str_repeat('a', 256)]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quality_checksums']);
    }

    // ==================== Show Tests ====================

    /**
     * Test user can view a specific dataset snapshot.
     */
    public function test_user_can_view_specific_dataset_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson("/api/dataset-snapshots/{$snapshot->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'dataset_id',
                    'version_tag',
                    'encryption_status',
                    'status',
                ],
            ])
            ->assertJson([
                'error' => false,
                'data' => [
                    'id' => $snapshot->id,
                    'version_tag' => $snapshot->version_tag,
                ],
            ]);
    }

    /**
     * Test show method includes dataset relationship when loaded.
     */
    public function test_show_method_includes_dataset_relationship(): void
    {
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);
        $snapshot = DatasetSnapshot::factory()->for($dataset)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson("/api/dataset-snapshots/{$snapshot->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'dataset' => [
                        'id',
                        'name',
                    ],
                ],
            ]);
    }

    // ==================== Update Tests ====================

    /**
     * Test user can update a dataset snapshot.
     */
    public function test_user_can_update_dataset_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'organization_id' => $this->organization->id,
            'version_tag' => 'v1.0',
            'row_count' => 1000,
            'status' => Status::ACTIVE->value,
        ]);

        $updateData = [
            'version_tag' => 'v1.1',
            'row_count' => 1500,
            'file_count' => 150,
            'status' => Status::DEPRECATED->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/dataset-snapshots/{$snapshot->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset snapshot updated successfully',
                'data' => [
                    'version_tag' => 'v1.1',
                    'row_count' => 1500,
                ],
            ]);

        $this->assertDatabaseHas('dataset_snapshots', [
            'id' => $snapshot->id,
            'version_tag' => 'v1.1',
            'row_count' => 1500,
            'file_count' => 150,
        ]);
    }

    /**
     * Test user can partially update a dataset snapshot.
     */
    public function test_user_can_partially_update_dataset_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'organization_id' => $this->organization->id,
            'version_tag' => 'v1.0',
            'row_count' => 1000,
            'residency_zone' => ResidencyZone::EU->value,
        ]);

        $updateData = [
            'row_count' => 2000,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/dataset-snapshots/{$snapshot->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('dataset_snapshots', [
            'id' => $snapshot->id,
            'version_tag' => 'v1.0', // unchanged
            'row_count' => 2000, // updated
            'residency_zone' => ResidencyZone::EU->value, // unchanged
        ]);
    }

    /**
     * Test update can change enum fields.
     */
    public function test_update_can_change_enum_fields(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'organization_id' => $this->organization->id,
            'compression' => Compression::GZIP->value,
            'status' => Status::ACTIVE->value,
        ]);

        $updateData = [
            'compression' => Compression::SNAPPY->value,
            'status' => Status::ARCHIVED->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/dataset-snapshots/{$snapshot->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('dataset_snapshots', [
            'id' => $snapshot->id,
            'compression' => Compression::SNAPPY->value,
            'status' => Status::ARCHIVED->value,
        ]);
    }

    /**
     * Test update validates time_range_end after time_range_start.
     */
    public function test_update_validates_time_range_end_after_start(): void
    {
        $snapshot = DatasetSnapshot::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = [
            'time_range_start' => '2024-12-31',
            'time_range_end' => '2024-01-01',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/dataset-snapshots/{$snapshot->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['time_range_end']);
    }

    // ==================== Delete Tests ====================

    /**
     * Test user can delete a dataset snapshot.
     */
    public function test_user_can_delete_dataset_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/dataset-snapshots/{$snapshot->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset snapshot deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('dataset_snapshots', ['id' => $snapshot->id]);
    }

    // ==================== Authentication Tests ====================

    /**
     * Test unauthenticated user cannot access index.
     */
    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/dataset-snapshots');

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot create dataset snapshot.
     */
    public function test_unauthenticated_user_cannot_create_dataset_snapshot(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot view dataset snapshot.
     */
    public function test_unauthenticated_user_cannot_view_dataset_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson("/api/dataset-snapshots/{$snapshot->id}");

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot update dataset snapshot.
     */
    public function test_unauthenticated_user_cannot_update_dataset_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->postJson("/api/dataset-snapshots/{$snapshot->id}", ['version_tag' => 'v2.0']);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot delete dataset snapshot.
     */
    public function test_unauthenticated_user_cannot_delete_dataset_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->deleteJson("/api/dataset-snapshots/{$snapshot->id}");

        $response->assertStatus(401);
    }
}
