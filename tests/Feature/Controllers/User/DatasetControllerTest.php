<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\Dataset\ContainsPii;
use App\Enums\Dataset\ControllerRole;
use App\Enums\Dataset\CrossBorderTransfer;
use App\Enums\Dataset\DataStructure;
use App\Enums\Dataset\DataSubjectCategory;
use App\Enums\Dataset\LawfulBasis;
use App\Enums\Dataset\LicenseType;
use App\Enums\Dataset\Purpose;
use App\Enums\Dataset\Sensitivity;
use App\Enums\Dataset\StorageFormat;
use App\Models\Dataset;
use App\Models\DataSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatasetControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function enumFirstValue(string $enumClass): string
    {
        return $enumClass::cases()[0]->value;
    }

    private function validPayload(array $overrides = []): array
    {
        $dataSources = DataSource::factory()->count(2)->create();

        return array_merge([
            'dataset_id' => 'DS-' . fake()->unique()->numerify('######'),
            'name' => 'Customer Analytics Dataset',
            'source_ids' => $dataSources->pluck('id')->toArray(),
            'purpose' => $this->enumFirstValue(Purpose::class),
            'schema_summary' => 'Contains customer demographic and transaction data',
            'sensitivity' => $this->enumFirstValue(Sensitivity::class),
            'contains_pii' => $this->enumFirstValue(ContainsPii::class),
            'data_subject_categories' => [$this->enumFirstValue(DataSubjectCategory::class)],
            'controller_role' => $this->enumFirstValue(ControllerRole::class),
            'lawful_basis' => $this->enumFirstValue(LawfulBasis::class),
            'lawful_basis_detail' => 'Necessary for legitimate business interests',
            'consent_required' => true,
            'consent_coverage_pct' => 95,
            'consent_source_ref' => 'consent-2024-001',
            'licensing_basis' => 'Internal use only',
            'license_type' => $this->enumFirstValue(LicenseType::class),
            'privacy_notice_ref' => 'PN-2024-001',
            'cross_border_transfer' => $this->enumFirstValue(CrossBorderTransfer::class),
            'data_structure' => $this->enumFirstValue(DataStructure::class),
            'storage_format' => $this->enumFirstValue(StorageFormat::class),
            'content_types' => ['text', 'structured'],
            'retention_policy_ref' => 'policy-90d',
            'dpia_ref' => 'DPIA-2024-001',
            'aia_ref' => 'AIA-2024-001',
            'owner_team' => 'Data Science',
            'refresh_cadence' => 'daily',
            'quality_SLA' => '99.9%',
            'catalog_asset_id' => 'CAT-123456',
            'catalog_uri' => 'https://catalog.example.com/dataset/123456',
        ], $overrides);
    }

    public function test_user_can_get_paginated_datasets(): void
    {
        Dataset::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/datasets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data',
                    'total',
                    'per_page',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Datasets retrieved successfully',
            ]);

        $this->assertEquals(20, $response->json('data.total'));
    }

    public function test_user_can_set_custom_per_page(): void
    {
        Dataset::factory()->count(30)->create();

        $response = $this->actingAs($this->user)->getJson('/api/datasets?per_page=5');

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.per_page'));
        $this->assertEquals(30, $response->json('data.total'));
    }

    public function test_user_can_create_dataset(): void
    {
        $data = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset created successfully',
                'data' => [
                    'name' => 'Customer Analytics Dataset',
                    'owner_team' => 'Data Science',
                    'consent_required' => true,
                    'consent_coverage_pct' => 95,
                ],
            ]);

        $this->assertDatabaseHas('datasets', [
            'name' => 'Customer Analytics Dataset',
            'owner_team' => 'Data Science',
        ]);
    }

    public function test_user_cannot_create_dataset_with_invalid_enum_values(): void
    {
        $data = $this->validPayload([
            'purpose' => 'invalid_purpose',
            'sensitivity' => 'invalid_sensitivity',
            'storage_format' => 'invalid_format',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purpose', 'sensitivity', 'storage_format']);
    }

    public function test_user_cannot_create_dataset_with_invalid_source_ids(): void
    {
        $data = $this->validPayload([
            'source_ids' => [99999, 88888], // Non-existent IDs
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_ids.0', 'source_ids.1']);
    }

    public function test_user_can_view_single_dataset(): void
    {
        $dataset = Dataset::factory()->create([
            'name' => 'Test Dataset',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/datasets/' . $dataset->id);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset retrieved successfully',
                'data' => [
                    'id' => $dataset->id,
                    'name' => 'Test Dataset',
                ],
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'name',
                    'source_ids',
                    'purpose',
                    'schema_summary',
                    'sensitivity',
                    'contains_pii',
                    'data_subject_categories',
                    'controller_role',
                    'lawful_basis',
                    'lawful_basis_detail',
                    'consent_required',
                    'consent_coverage_pct',
                    'consent_source_ref',
                    'licensing_basis',
                    'license_type',
                    'privacy_notice_ref',
                    'cross_border_transfer',
                    'data_structure',
                    'storage_format',
                    'content_types',
                    'retention_policy_ref',
                    'dpia_ref',
                    'aia_ref',
                    'owner_team',
                    'refresh_cadence',
                    'quality_SLA',
                    'catalog_asset_id',
                    'catalog_uri',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_user_can_update_dataset(): void
    {
        $dataset = Dataset::factory()->create([
            'name' => 'Old Dataset Name',
            'owner_team' => 'Old Team',
            'consent_coverage_pct' => 75,
        ]);

        $updateData = [
            'name' => 'Updated Dataset Name',
            'owner_team' => 'Updated Team',
            'consent_coverage_pct' => 90,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/datasets/' . $dataset->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset updated successfully',
                'data' => [
                    'name' => 'Updated Dataset Name',
                    'owner_team' => 'Updated Team',
                    'consent_coverage_pct' => 90,
                ],
            ]);

        $this->assertDatabaseHas('datasets', [
            'id' => $dataset->id,
            'name' => 'Updated Dataset Name',
            'owner_team' => 'Updated Team',
        ]);
    }

    public function test_user_cannot_update_dataset_with_invalid_enum_values(): void
    {
        $dataset = Dataset::factory()->create();

        $updateData = [
            'sensitivity' => 'invalid_sensitivity',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/datasets/' . $dataset->id, $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sensitivity']);
    }

    public function test_user_can_delete_dataset(): void
    {
        $dataset = Dataset::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson('/api/datasets/' . $dataset->id);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset deleted successfully',
            ]);

        $this->assertDatabaseMissing('datasets', [
            'id' => $dataset->id,
        ]);
    }

    public function test_source_ids_are_stored_as_array(): void
    {
        $dataSources = DataSource::factory()->count(3)->create();
        $data = $this->validPayload([
            'source_ids' => $dataSources->pluck('id')->toArray(),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(201);

        $dataset = Dataset::find($response->json('data.id'));
        $this->assertIsArray($dataset->source_ids);
        $this->assertCount(3, $dataset->source_ids);
    }

    public function test_data_subject_categories_are_stored_as_array(): void
    {
        $data = $this->validPayload([
            'data_subject_categories' => [
                DataSubjectCategory::cases()[0]->value,
                DataSubjectCategory::cases()[1]->value,
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(201);

        $dataset = Dataset::find($response->json('data.id'));
        $this->assertIsArray($dataset->data_subject_categories);
        $this->assertCount(2, $dataset->data_subject_categories);
    }

    public function test_content_types_are_stored_as_array(): void
    {
        $data = $this->validPayload([
            'content_types' => ['text', 'image', 'video'],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(201);

        $dataset = Dataset::find($response->json('data.id'));
        $this->assertIsArray($dataset->content_types);
        $this->assertCount(3, $dataset->content_types);
    }

    public function test_nullable_fields_can_be_null(): void
    {
        $data = $this->validPayload([
            'schema_summary' => null,
            'lawful_basis_detail' => null,
            'consent_coverage_pct' => null,
            'consent_source_ref' => null,
            'licensing_basis' => null,
            'license_type' => null,
            'privacy_notice_ref' => null,
            'content_types' => null,
            'retention_policy_ref' => null,
            'dpia_ref' => null,
            'aia_ref' => null,
            'refresh_cadence' => null,
            'quality_SLA' => null,
            'catalog_asset_id' => null,
            'catalog_uri' => null,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(201);

        $dataset = Dataset::find($response->json('data.id'));
        $this->assertNull($dataset->schema_summary);
        $this->assertNull($dataset->lawful_basis_detail);
        $this->assertNull($dataset->consent_coverage_pct);
        $this->assertNull($dataset->consent_source_ref);
        $this->assertNull($dataset->licensing_basis);
        $this->assertNull($dataset->license_type);
        $this->assertNull($dataset->privacy_notice_ref);
        $this->assertNull($dataset->content_types);
        $this->assertNull($dataset->retention_policy_ref);
        $this->assertNull($dataset->dpia_ref);
        $this->assertNull($dataset->aia_ref);
        $this->assertNull($dataset->refresh_cadence);
        $this->assertNull($dataset->quality_SLA);
        $this->assertNull($dataset->catalog_asset_id);
        $this->assertNull($dataset->catalog_uri);
    }

    public function test_catalog_uri_must_be_valid_url(): void
    {
        $data = $this->validPayload([
            'catalog_uri' => 'not-a-valid-url',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['catalog_uri']);
    }

    public function test_consent_coverage_pct_must_be_between_0_and_100(): void
    {
        $data = $this->validPayload([
            'consent_coverage_pct' => 150, // Invalid: > 100
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['consent_coverage_pct']);

        $data = $this->validPayload([
            'consent_coverage_pct' => -10, // Invalid: < 0
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['consent_coverage_pct']);
    }

    public function test_consent_required_must_be_boolean(): void
    {
        $data = $this->validPayload([
            'consent_required' => 'yes', // Invalid: not a boolean
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['consent_required']);
    }
}
