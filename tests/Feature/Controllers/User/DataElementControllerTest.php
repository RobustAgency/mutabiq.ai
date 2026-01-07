<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Dataset;
use App\Models\DataSource;
use App\Models\DataElement;
use App\Models\Organization;
use App\Enums\DataElement\Status;
use App\Models\DatasetDataElement;
use App\Enums\DataElement\DataSteward;
use App\Enums\DataElement\Sensitivity;
use App\Enums\DataElement\PersonalDataCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataElementControllerTest extends TestCase
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
        $dataSource = DataSource::first() ?? DataSource::factory()->create();

        return array_merge([
            'name' => 'Customer Email',
            'business_definition' => 'Email address of the customer',
            'data_type' => 'string',
            'format' => 'email',
            'data_steward' => DataSteward::DATA_ENGINEERING_TEAM->value,
            'status' => Status::ACTIVE->value,
            'data_source_id' => $dataSource->id,
            'database_name' => 'prod_db',
            'schema_name' => 'public',
            'table_name' => 'customers',
            'column_name' => 'email',
            'sensitivity' => Sensitivity::CONFIDENTIAL->value,
            'contains_personal_data' => true,
            'personal_data_type' => PersonalDataCategory::CONTACT_INFORMATION->value,
            'contains_sensitive_data' => false,
            'cde_flag' => true,
            'cde_categories' => ['strategic_asset'],
        ], $overrides);
    }

    public function test_user_can_get_paginated_data_elements(): void
    {
        DataElement::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/data-elements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'business_definition',
                            'data_steward',
                            'status',
                            'data_source_id',
                            'database_name',
                            'schema_name',
                            'table_name',
                            'column_name',
                            'used_in_datasets',
                            'is_nullable',
                            'is_unique',
                            'default_value',
                            'validation_rule',
                            'sample_values',
                            'data_type',
                            'format',
                            'sensitivity',
                            'contains_personal_data',
                            'personal_data_type',
                            'contains_sensitive_data',
                            'default_masking_method',
                            'cde_flag',
                            'cde_categories',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'total',
                    'per_page',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Data elements retrieved successfully',
            ]);

        $this->assertEquals(20, $response->json('data.total'));
    }

    public function test_user_can_set_custom_per_page(): void
    {
        DataElement::factory()->count(30)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/data-elements?per_page=5');

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.per_page'));
        $this->assertEquals(30, $response->json('data.total'));
    }

    public function test_user_can_create_data_element(): void
    {
        $data = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/data-elements', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Data element created successfully',
                'data' => [
                    'name' => 'Customer Email',
                    'business_definition' => 'Email address of the customer',
                    'data_type' => 'string',
                    'sensitivity' => Sensitivity::CONFIDENTIAL->value,
                    'contains_personal_data' => true,
                ],
            ]);

        $this->assertDatabaseHas('data_elements', [
            'name' => 'Customer Email',
            'data_type' => 'string',
            'sensitivity' => Sensitivity::CONFIDENTIAL->value,
        ]);
    }

    public function test_user_cannot_create_data_element_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/data-elements', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'business_definition',
                'data_type',
                'data_steward',
                'status',
                'data_source_id',
                'database_name',
                'table_name',
                'column_name',
                'sensitivity',
                'contains_personal_data',
            ]);
    }

    public function test_user_can_view_single_data_element(): void
    {
        $dataElement = DataElement::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Element',
            'data_type' => 'string',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/data-elements/'.$dataElement->id);

        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'name',
                'business_definition',
                'data_steward',
                'status',
                'data_source_id',
                'database_name',
                'schema_name',
                'table_name',
                'column_name',
                'used_in_datasets',
                'is_nullable',
                'is_unique',
                'default_value',
                'validation_rule',
                'sample_values',
                'data_type',
                'format',
                'sensitivity',
                'contains_personal_data',
                'personal_data_type',
                'contains_sensitive_data',
                'default_masking_method',
                'cde_flag',
                'cde_categories',
                'data_source',
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Data element retrieved successfully',
                'data' => [
                    'id' => $dataElement->id,
                    'name' => 'Test Element',
                    'data_type' => 'string',
                ],
            ]);
    }

    public function test_user_can_update_data_element(): void
    {
        $dataElement = DataElement::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Old Name',
            'sensitivity' => Sensitivity::INTERNAL->value,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'sensitivity' => Sensitivity::CONFIDENTIAL->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-elements/'.$dataElement->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Data element updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'sensitivity' => Sensitivity::CONFIDENTIAL->value,
                ],
            ]);

        $this->assertDatabaseHas('data_elements', [
            'id' => $dataElement->id,
            'name' => 'Updated Name',
            'sensitivity' => Sensitivity::CONFIDENTIAL->value,
        ]);
    }

    public function test_user_can_delete_data_element(): void
    {
        $dataElement = DataElement::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson('/api/data-elements/'.$dataElement->id);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Data element deleted successfully',
            ]);

        $this->assertDatabaseMissing('data_elements', [
            'id' => $dataElement->id,
        ]);
    }

    public function test_deleting_data_element_removes_dataset_associations(): void
    {
        $dataElement = DataElement::factory()->create(['organization_id' => $this->organization->id]);
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);

        $association = DatasetDataElement::factory()->create([
            'organization_id' => $this->organization->id,
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson('/api/data-elements/'.$dataElement->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('data_elements', ['id' => $dataElement->id]);
        $this->assertDatabaseMissing('dataset_element', ['id' => $association->id]);
    }

    public function test_data_element_name_is_required(): void
    {
        $data = $this->validPayload(['name' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/data-elements', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_data_element_name_must_be_string(): void
    {
        $data = $this->validPayload(['name' => 12345]);

        $response = $this->actingAs($this->user)->postJson('/api/data-elements', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_updating_data_element_preserves_dataset_associations(): void
    {
        $dataElement = DataElement::factory()->create(['organization_id' => $this->organization->id, 'name' => 'Original']);
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);

        DatasetDataElement::factory()->create([
            'organization_id' => $this->organization->id,
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-elements/'.$dataElement->id, [
            'name' => 'Updated',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('dataset_element', [
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);
    }
}
