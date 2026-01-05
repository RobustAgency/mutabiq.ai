<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Dataset;
use App\Models\DataElement;
use App\Models\Organization;
use App\Models\DatasetDataElement;
use App\Repositories\DataElementRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataElementRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DataElementRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(DataElementRepository::class);
    }

    public function test_get_paginated_data_elements_returns_paginated_results(): void
    {
        $organization = Organization::factory()->create();
        DataElement::factory()->count(25)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDataElements(['organization_id' => $organization->id, 'per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }

    public function test_get_paginated_data_elements_eager_loads_datasets(): void
    {
        $organization = Organization::factory()->create();
        $dataElement = DataElement::factory()->create(['organization_id' => $organization->id]);
        $dataset = Dataset::factory()->create(['organization_id' => $organization->id]);

        DatasetDataElement::factory()->create([
            'organization_id' => $organization->id,
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);

        $result = $this->repository->getFilteredDataElements(['organization_id' => $organization->id]);
        $firstElement = $result->items()[0];

        $this->assertTrue($firstElement->relationLoaded('datasets'));
        $this->assertCount(1, $firstElement->datasets);
    }

    public function test_get_paginated_data_elements_uses_default_per_page(): void
    {
        $organization = Organization::factory()->create();
        DataElement::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDataElements(['organization_id' => $organization->id]);

        $this->assertCount(15, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_create_data_element_creates_new_record(): void
    {
        $organization = Organization::factory()->create();
        $dataSource = \App\Models\DataSource::factory()->create(['organization_id' => $organization->id]);

        $data = [
            'organization_id' => $organization->id,
            'name' => 'Customer ID',
            'business_definition' => 'Unique identifier for customers',
            'data_type' => 'string',
            'format' => 'UUID',
            'data_steward' => 'data_engineer',
            'status' => 'active',
            'data_source_id' => $dataSource->id,
            'database_name' => 'prod_db',
            'schema_name' => 'public',
            'table_name' => 'customers',
            'column_name' => 'customer_id',
            'used_in_datasets' => json_encode([]),
            'is_nullable' => false,
            'is_unique' => true,
            'default_value' => null,
            'validation_rule' => 'NOT NULL, UNIQUE',
            'sample_values' => json_encode(['CUST001', 'CUST002']),
            'sensitivity' => 'confidential',
            'contains_personal_data' => true,
            'personal_data_type' => 'identifier',
            'contains_sensitive_data' => false,
            'default_masking_method' => 'tokenization',
            'cde_flag' => true,
            'cde_categories' => json_encode(['strategic_asset']),
        ];

        $dataElement = $this->repository->createDataElement($data);

        $this->assertInstanceOf(DataElement::class, $dataElement);
        $this->assertEquals('Customer ID', $dataElement->name);
        $this->assertDatabaseHas('data_elements', ['name' => 'Customer ID']);
    }

    public function test_update_data_element_updates_existing_record(): void
    {
        $dataElement = DataElement::factory()->create([
            'name' => 'Original Name',
            'sensitivity' => 'internal',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'sensitivity' => 'confidential',
        ];

        $updated = $this->repository->updateDataElement($dataElement, $updateData);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('confidential', $updated->sensitivity);
        $this->assertDatabaseHas('data_elements', [
            'id' => $dataElement->id,
            'name' => 'Updated Name',
            'sensitivity' => 'confidential',
        ]);
    }

    public function test_update_data_element_returns_fresh_instance(): void
    {
        $dataElement = DataElement::factory()->create(['name' => 'Old Name']);

        $updated = $this->repository->updateDataElement($dataElement, ['name' => 'New Name']);

        $this->assertNotSame($dataElement, $updated);
        $this->assertEquals('New Name', $updated->name);
    }

    public function test_delete_removes_data_element(): void
    {
        $dataElement = DataElement::factory()->create();

        $result = $this->repository->delete($dataElement);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('data_elements', ['id' => $dataElement->id]);
    }

    public function test_delete_returns_false_on_failure(): void
    {
        $dataElement = DataElement::factory()->create();
        $dataElement->delete();

        $result = $this->repository->delete($dataElement);

        $this->assertFalse($result);
    }

    public function test_get_data_element_by_id_returns_correct_element(): void
    {
        $organization = Organization::factory()->create();
        $dataElement = DataElement::factory()->create(['name' => 'Test Element', 'organization_id' => $organization->id]);

        $result = $this->repository->getDataElementByID($dataElement->id);

        $this->assertInstanceOf(DataElement::class, $result);
        $this->assertEquals($dataElement->id, $result->id);
        $this->assertEquals('Test Element', $result->name);
    }

    public function test_data_element_can_have_multiple_datasets(): void
    {
        $organization = Organization::factory()->create();
        $dataElement = DataElement::factory()->create(['organization_id' => $organization->id]);
        $dataset1 = Dataset::factory()->create(['organization_id' => $organization->id]);
        $dataset2 = Dataset::factory()->create(['organization_id' => $organization->id]);

        DatasetDataElement::factory()->create([
            'organization_id' => $organization->id,
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset1->id,
            'column_name' => 'col1',
        ]);

        DatasetDataElement::factory()->create([
            'organization_id' => $organization->id,
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset2->id,
            'column_name' => 'col2',
        ]);

        $dataElement->load('datasets');

        $this->assertCount(2, $dataElement->datasets);
        $this->assertTrue($dataElement->datasets->contains($dataset1));
        $this->assertTrue($dataElement->datasets->contains($dataset2));
    }

    public function test_data_element_datasets_relationship_includes_pivot_data(): void
    {
        $organization = Organization::factory()->create();
        $dataElement = DataElement::factory()->create(['organization_id' => $organization->id]);
        $dataset = Dataset::factory()->create(['organization_id' => $organization->id]);

        DatasetDataElement::factory()->create([
            'organization_id' => $organization->id,
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
            'column_name' => 'test_column',
            'nullable' => 'Yes',
            'pii_override' => 'No',
            'cde_in_dataset' => 'Yes',
        ]);

        $dataElement->load('datasets');
        $relatedDataset = $dataElement->datasets->first();

        $this->assertNotNull($relatedDataset->pivot);
        $this->assertEquals('test_column', $relatedDataset->pivot->column_name);
        $this->assertEquals('Yes', $relatedDataset->pivot->nullable);
        $this->assertEquals('No', $relatedDataset->pivot->pii_override);
        $this->assertEquals('Yes', $relatedDataset->pivot->cde_in_dataset);
    }

    public function test_deleting_data_element_cascades_to_associations(): void
    {
        $organization = Organization::factory()->create();
        $dataElement = DataElement::factory()->create(['organization_id' => $organization->id]);
        $dataset = Dataset::factory()->create(['organization_id' => $organization->id]);

        $association = DatasetDataElement::factory()->create([
            'organization_id' => $organization->id,
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);

        $this->repository->delete($dataElement);

        $this->assertDatabaseMissing('data_elements', ['id' => $dataElement->id]);
        $this->assertDatabaseMissing('dataset_element', ['id' => $association->id]);
    }
}
