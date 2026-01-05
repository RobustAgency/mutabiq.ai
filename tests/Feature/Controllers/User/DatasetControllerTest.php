<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Dataset;
use App\Models\DataSource;
use App\Models\Organization;
use App\Enums\Dataset\Status;
use App\Enums\Dataset\Purpose;
use App\Enums\Dataset\SizeUnit;
use App\Enums\Dataset\OwnerTeam;
use App\Enums\Dataset\DataSteward;
use App\Enums\Dataset\LicenseType;
use App\Enums\Dataset\Sensitivity;
use App\Enums\Dataset\PrimaryLanguage;
use App\Enums\Dataset\ContainPersonalData;
use App\Enums\Dataset\CrossBorderTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatasetControllerTest extends TestCase
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

    private function enumFirstValue(string $enumClass): string
    {
        return $enumClass::cases()[0]->value;
    }

    private function validPayload(array $overrides = []): array
    {
        $dataSources = DataSource::factory()->count(2)->create(['organization_id' => $this->organization->id]);

        return array_merge([
            'name' => 'Customer Analytics Dataset',
            'description' => 'Contains customer demographic and transaction data',
            'source_ids' => $dataSources->pluck('id')->toArray(),
            'purpose' => $this->enumFirstValue(Purpose::class),
            'owner_team' => OwnerTeam::ML_PLATFORM_TEAM->value,
            'data_steward' => $this->enumFirstValue(DataSteward::class),
            'status' => $this->enumFirstValue(Status::class),
            'estimated_row_count' => 1000000,
            'estimated_size' => 50,
            'size_unit' => $this->enumFirstValue(SizeUnit::class),
            'retention_period' => '90 days',
            'primary_languages' => [
                $this->enumFirstValue(PrimaryLanguage::class),
            ],
            'contains_personal_data' => $this->enumFirstValue(ContainPersonalData::class),
            'sensitivity' => $this->enumFirstValue(Sensitivity::class),
            'cross_border_transfer' => $this->enumFirstValue(CrossBorderTransfer::class),
            'license_type' => $this->enumFirstValue(LicenseType::class),
        ], $overrides);
    }

    public function test_user_can_get_paginated_datasets(): void
    {
        Dataset::factory()->count(20)->create(['organization_id' => $this->organization->id]);

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
        Dataset::factory()->count(30)->create(['organization_id' => $this->organization->id]);

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
                    'owner_team' => 'ml_platform_team',
                    'estimated_row_count' => 1000000,
                    'retention_period' => '90 days',
                ],
            ]);

        $this->assertDatabaseHas('datasets', [
            'name' => 'Customer Analytics Dataset',
            'owner_team' => OwnerTeam::ML_PLATFORM_TEAM->value,
        ]);
    }

    public function test_user_cannot_create_dataset_with_invalid_enum_values(): void
    {
        $data = $this->validPayload([
            'purpose' => 'invalid_purpose',
            'sensitivity' => 'invalid_sensitivity',
            'status' => 'invalid_status',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purpose', 'sensitivity', 'status']);
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
            'organization_id' => $this->organization->id,
            'name' => 'Test Dataset',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/datasets/'.$dataset->id);

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
                    'display_id',
                    'name',
                    'description',
                    'source_ids',
                    'purpose',
                    'owner_team',
                    'data_steward',
                    'status',
                    'estimated_row_count',
                    'estimated_size',
                    'size_unit',
                    'retention_period',
                    'primary_languages',
                    'contains_personal_data',
                    'sensitivity',
                    'cross_border_transfer',
                    'license_type',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_user_can_update_dataset(): void
    {
        $dataset = Dataset::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Old Dataset Name',
            'owner_team' => 'Old Team',
            'retention_period' => '30 days',
        ]);

        $updateData = [
            'name' => 'Updated Dataset Name',
            'retention_period' => '90 days',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/datasets/'.$dataset->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset updated successfully',
                'data' => [
                    'name' => 'Updated Dataset Name',
                    'retention_period' => '90 days',
                ],
            ]);

        $this->assertDatabaseHas('datasets', [
            'id' => $dataset->id,
            'name' => 'Updated Dataset Name',
        ]);
    }

    public function test_user_cannot_update_dataset_with_invalid_enum_values(): void
    {
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = [
            'sensitivity' => 'invalid_sensitivity',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/datasets/'.$dataset->id, $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sensitivity']);
    }

    public function test_user_can_delete_dataset(): void
    {
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson('/api/datasets/'.$dataset->id);

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

    public function test_primary_languages_are_stored_as_array(): void
    {
        $data = $this->validPayload([
            'primary_languages' => [
                PrimaryLanguage::cases()[0]->value,
                PrimaryLanguage::cases()[1]->value,
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(201);

        $dataset = Dataset::find($response->json('data.id'));
        $this->assertIsArray($dataset->primary_languages);
        $this->assertCount(2, $dataset->primary_languages);
    }

    public function test_estimated_row_count_must_be_integer(): void
    {
        $data = $this->validPayload([
            'estimated_row_count' => 'not_an_integer',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['estimated_row_count']);
    }

    public function test_retention_period_must_be_string(): void
    {
        $data = $this->validPayload([
            'retention_period' => 123, // Invalid: not a string
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/datasets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['retention_period']);
    }
}
