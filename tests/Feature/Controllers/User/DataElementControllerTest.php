<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\DataElement\CdeCategory;
use App\Enums\DataElement\PersonalDataCategory;
use App\Models\DataElement;
use App\Models\Dataset;
use App\Models\DatasetDataElement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataElementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Customer Email',
            'business_definition' => 'Email address of the customer',
            'data_type' => 'string',
            'format' => 'email',
            'sensitivity' => 'Confidential',
            'pii_flag' => 'Yes',
            'personal_data_category' => PersonalDataCategory::IDENTIFIER->value,
            'special_category_flag' => 'No',
            'cde_flag' => 'Yes',
            'cde_category' => CdeCategory::FINANCIAL->value,
            'owner_team' => 'Data Engineering',
            'quality_rules_ref' => 'Must be valid email format',
            'catalog_column_id' => 'COL000123',
        ], $overrides);
    }

    public function test_user_can_get_paginated_data_elements(): void
    {
        DataElement::factory()->count(20)->create();

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
                            'data_type',
                            'format',
                            'sensitivity',
                            'pii_flag',
                            'personal_data_category',
                            'special_category_flag',
                            'cde_flag',
                            'cde_category',
                            'owner_team',
                            'quality_rules_ref',
                            'catalog_column_id',
                            'created_at',
                            'updated_at',
                            'datasets' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'pivot' => [
                                        'dataset_id',
                                        'data_element_id',
                                        'column_name',
                                        'nullable',
                                    ],
                                ],
                            ],
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
        DataElement::factory()->count(30)->create();

        $response = $this->actingAs($this->user)->getJson('/api/data-elements?per_page=5');

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.per_page'));
        $this->assertEquals(30, $response->json('data.total'));
    }

    public function test_paginated_data_elements_include_datasets_relationship(): void
    {
        $dataElement = DataElement::factory()->create();
        $dataset = Dataset::factory()->create();

        DatasetDataElement::factory()->create([
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/data-elements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'datasets',
                        ],
                    ],
                ],
            ]);
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
                    'sensitivity' => 'Confidential',
                    'pii_flag' => 'Yes',
                ],
            ]);

        $this->assertDatabaseHas('data_elements', [
            'name' => 'Customer Email',
            'data_type' => 'string',
            'sensitivity' => 'Confidential',
        ]);
    }

    public function test_user_cannot_create_data_element_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/data-elements', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'data_type',
                'sensitivity',
                'pii_flag',
                'special_category_flag',
                'cde_flag',
            ]);
    }

    public function test_user_can_view_single_data_element(): void
    {
        $dataElement = DataElement::factory()->create([
            'name' => 'Test Element',
            'data_type' => 'string',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/data-elements/' . $dataElement->id);

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

    public function test_show_method_includes_datasets_relationship(): void
    {
        $dataElement = DataElement::factory()->create(['name' => 'Test Element']);
        $dataset = Dataset::factory()->create(['name' => 'Test Dataset']);

        DatasetDataElement::factory()->create([
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
            'column_name' => 'customer_email',
            'nullable' => 'No',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/data-elements/' . $dataElement->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'datasets' => [
                        '*' => [
                            'id',
                            'name',
                            'pivot' => [
                                'dataset_id',
                                'data_element_id',
                                'column_name',
                                'nullable',
                            ],
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'datasets' => [
                        [
                            'id' => $dataset->id,
                            'name' => 'Test Dataset',
                            'pivot' => [
                                'column_name' => 'customer_email',
                                'nullable' => 'No',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_user_can_update_data_element(): void
    {
        $dataElement = DataElement::factory()->create([
            'name' => 'Old Name',
            'sensitivity' => 'Internal',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'sensitivity' => 'Confidential',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-elements/' . $dataElement->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Data element updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'sensitivity' => 'Confidential',
                ],
            ]);

        $this->assertDatabaseHas('data_elements', [
            'id' => $dataElement->id,
            'name' => 'Updated Name',
            'sensitivity' => 'Confidential',
        ]);
    }

    public function test_user_can_delete_data_element(): void
    {
        $dataElement = DataElement::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson('/api/data-elements/' . $dataElement->id);

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
        $dataElement = DataElement::factory()->create();
        $dataset = Dataset::factory()->create();

        $association = DatasetDataElement::factory()->create([
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson('/api/data-elements/' . $dataElement->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('data_elements', ['id' => $dataElement->id]);
        $this->assertDatabaseMissing('dataset_element', ['id' => $association->id]);
    }

    public function test_nullable_fields_can_be_null(): void
    {
        $data = $this->validPayload([
            'format' => null,
            'personal_data_category' => null,
            'cde_category' => null,
            'owner_team' => null,
            'quality_rules_ref' => null,
            'catalog_column_id' => null,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-elements', $data);

        $response->assertStatus(201);

        $dataElement = DataElement::find($response->json('data.id'));
        $this->assertNull($dataElement->format);
        $this->assertNull($dataElement->personal_data_category);
        $this->assertNull($dataElement->cde_category);
        $this->assertNull($dataElement->owner_team);
        $this->assertNull($dataElement->quality_rules_ref);
        $this->assertNull($dataElement->catalog_column_id);
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

    public function test_single_data_element_includes_datasets_with_pivot_data(): void
    {
        $dataElement = DataElement::factory()->create();
        $dataset = Dataset::factory()->create(['name' => 'Test Dataset']);

        DatasetDataElement::factory()->create([
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
            'column_name' => 'customer_email',
            'nullable' => 'No',
        ]);

        $dataElement->load('datasets');

        $response = $this->actingAs($this->user)->getJson('/api/data-elements/' . $dataElement->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'datasets' => [
                        '*' => [
                            'id',
                            'name',
                            'pivot' => [
                                'column_name',
                                'nullable',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_updating_data_element_preserves_dataset_associations(): void
    {
        $dataElement = DataElement::factory()->create(['name' => 'Original']);
        $dataset = Dataset::factory()->create();

        DatasetDataElement::factory()->create([
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-elements/' . $dataElement->id, [
            'name' => 'Updated',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('dataset_element', [
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);
    }
}
