<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\DatasetSnapshot\ResidencyZone;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            'time_range_start' => '2024-01-01',
            'time_range_end' => '2024-12-31',
            'row_count' => 50000,
            'quality_checksums' => hash('sha256', 'test'),
            'pii_element_count' => 10,
            'special_category_element_count' => 5,
            'masking_anonymization_method' => 'Tokenization',
            'privacy_transform_evidence_ref' => 'PTE-123456',
            'residency_zone' => ResidencyZone::EU->value,
            'storage_uri' => 'https://storage.example.com/snapshots/abc123',
            'source_created_at' => '2024-01-15',
        ], $overrides);
    }

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
                            'time_range_start',
                            'time_range_end',
                            'row_count',
                            'quality_checksums',
                            'pii_element_count',
                            'special_category_element_count',
                            'masking_anonymization_method',
                            'privacy_transform_evidence_ref',
                            'residency_zone',
                            'storage_uri',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'per_page',
                    'total',
                ]
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
                    'version_tag',
                    'residency_zone',
                    'storage_uri',
                ]
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Dataset snapshot created successfully',
            ]);

        $this->assertDatabaseHas('dataset_snapshots', [
            'dataset_id' => $payload['dataset_id'],
            'version_tag' => $payload['version_tag'],
            'residency_zone' => $payload['residency_zone'],
            'storage_uri' => $payload['storage_uri'],
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
            'residency_zone' => ResidencyZone::US->value,
            'storage_uri' => 'https://storage.example.com/snapshots/xyz789',
            'source_created_at' => '2024-01-15',
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
     * Test special_category_element_count must be non-negative.
     */
    public function test_special_category_element_count_must_be_non_negative(): void
    {
        $payload = $this->validPayload(['special_category_element_count' => -1]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['special_category_element_count']);
    }

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
                    'version_tag',
                    'residency_zone',
                    'storage_uri',
                ]
            ])
            ->assertJson([
                'error' => false,
                'data' => [
                    'id' => $snapshot->id,
                    'version_tag' => $snapshot->version_tag,
                ]
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
                    ]
                ]
            ]);
    }

    /**
     * Test user can update a dataset snapshot.
     */
    public function test_user_can_update_dataset_snapshot(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'organization_id' => $this->organization->id,
            'version_tag' => 'v1.0',
            'row_count' => 1000,
        ]);

        $updateData = [
            'version_tag' => 'v1.1',
            'row_count' => 1500,
            'quality_checksums' => hash('sha256', 'updated'),
        ];

        $response = $this->actingAs($this->user)->postJson("/api/dataset-snapshots/{$snapshot->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset snapshot updated successfully',
                'data' => [
                    'version_tag' => 'v1.1',
                    'row_count' => 1500,
                ]
            ]);

        $this->assertDatabaseHas('dataset_snapshots', [
            'id' => $snapshot->id,
            'version_tag' => 'v1.1',
            'row_count' => 1500,
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
            'residency_zone' => ResidencyZone::EU,
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

    /**
     * Test masking_anonymization_method max length is 255.
     */
    public function test_masking_anonymization_method_max_length_is_255(): void
    {
        $payload = $this->validPayload(['masking_anonymization_method' => str_repeat('a', 256)]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['masking_anonymization_method']);
    }

    /**
     * Test privacy_transform_evidence_ref max length is 255.
     */
    public function test_privacy_transform_evidence_ref_max_length_is_255(): void
    {
        $payload = $this->validPayload(['privacy_transform_evidence_ref' => str_repeat('a', 256)]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-snapshots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['privacy_transform_evidence_ref']);
    }
}
