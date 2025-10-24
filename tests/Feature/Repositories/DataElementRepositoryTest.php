<?php

namespace Tests\Feature\Repositories;

use App\Models\DataElement;
use App\Models\Dataset;
use App\Models\DatasetDataElement;
use App\Repositories\DataElementRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        DataElement::factory()->count(25)->create();

        $result = $this->repository->getPaginatedDataElements(10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
    }

    public function test_get_paginated_data_elements_eager_loads_datasets(): void
    {
        $dataElement = DataElement::factory()->create();
        $dataset = Dataset::factory()->create();

        DatasetDataElement::factory()->create([
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);

        $result = $this->repository->getPaginatedDataElements();
        $firstElement = $result->items()[0];

        $this->assertTrue($firstElement->relationLoaded('datasets'));
        $this->assertCount(1, $firstElement->datasets);
    }

    public function test_get_paginated_data_elements_uses_default_per_page(): void
    {
        DataElement::factory()->count(20)->create();

        $result = $this->repository->getPaginatedDataElements();

        $this->assertCount(15, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_create_data_element_creates_new_record(): void
    {
        $data = [
            'name' => 'Customer ID',
            'business_definition' => 'Unique identifier for customers',
            'data_type' => 'string',
            'format' => 'UUID',
            'sensitivity' => 'Confidential',
            'pii_flag' => 'Yes',
            'personal_data_category' => 'Identifier',
            'special_category_flag' => 'No',
            'cde_flag' => 'Yes',
            'cde_category' => 'Strategic',
            'owner_team' => 'Data Engineering',
            'quality_rules_ref' => 'Must be unique and not null',
            'catalog_column_id' => 'COL000123',
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
            'sensitivity' => 'Internal',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'sensitivity' => 'Confidential',
        ];

        $updated = $this->repository->updateDataElement($dataElement, $updateData);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('Confidential', $updated->sensitivity);
        $this->assertDatabaseHas('data_elements', [
            'id' => $dataElement->id,
            'name' => 'Updated Name',
            'sensitivity' => 'Confidential',
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

    public function test_get_paginated_data_elements_returns_empty_when_no_records(): void
    {
        $result = $this->repository->getPaginatedDataElements();

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    public function test_get_data_element_by_id_returns_correct_element(): void
    {
        $dataElement = DataElement::factory()->create(['name' => 'Test Element']);

        $result = $this->repository->getDataElementByID($dataElement->id);

        $this->assertInstanceOf(DataElement::class, $result);
        $this->assertEquals($dataElement->id, $result->id);
        $this->assertEquals('Test Element', $result->name);
    }

    public function test_get_data_element_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getDataElementByID(99999);

        $this->assertNull($result);
    }

    public function test_get_data_element_by_id_eager_loads_datasets(): void
    {
        $dataElement = DataElement::factory()->create(['name' => 'Test Element']);
        $dataset = Dataset::factory()->create();

        DatasetDataElement::factory()->create([
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
            'column_name' => 'test_column',
        ]);

        $result = $this->repository->getDataElementByID($dataElement->id);

        $this->assertInstanceOf(DataElement::class, $result);
        $this->assertTrue($result->relationLoaded('datasets'));
        $this->assertCount(1, $result->datasets);
        $this->assertEquals($dataset->id, $result->datasets->first()->id);
    }

    public function test_associate_data_element_with_dataset_creates_association(): void
    {
        $dataset = Dataset::factory()->create();
        $dataElement = DataElement::factory()->create();

        $data = [
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement->id,
            'column_name' => 'customer_email',
            'nullable' => 'No',
            'sensitivity_override' => null,
            'pii_override' => 'Yes',
            'transform_applied' => 'Encrypted',
            'quality_rules_applied' => 'Must be valid email format',
            'cde_in_dataset' => 'Yes',
            'cde_category_in_dataset' => 'Demographics',
            'lineage_source_column' => 'source.email',
            'deprecated' => 'No',
        ];

        $association = $this->repository->associateDataElementWithDataset($data);

        $this->assertInstanceOf(DatasetDataElement::class, $association);
        $this->assertEquals($dataset->id, $association->dataset_id);
        $this->assertEquals($dataElement->id, $association->data_element_id);
        $this->assertEquals('customer_email', $association->column_name);
        $this->assertDatabaseHas('dataset_element', [
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement->id,
            'column_name' => 'customer_email',
        ]);
    }

    public function test_data_element_can_have_multiple_datasets(): void
    {
        $dataElement = DataElement::factory()->create();
        $dataset1 = Dataset::factory()->create();
        $dataset2 = Dataset::factory()->create();

        DatasetDataElement::factory()->create([
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset1->id,
            'column_name' => 'col1',
        ]);

        DatasetDataElement::factory()->create([
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
        $dataElement = DataElement::factory()->create();
        $dataset = Dataset::factory()->create();

        DatasetDataElement::factory()->create([
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
        $dataElement = DataElement::factory()->create();
        $dataset = Dataset::factory()->create();

        $association = DatasetDataElement::factory()->create([
            'data_element_id' => $dataElement->id,
            'dataset_id' => $dataset->id,
        ]);

        $this->repository->delete($dataElement);

        $this->assertDatabaseMissing('data_elements', ['id' => $dataElement->id]);
        $this->assertDatabaseMissing('dataset_element', ['id' => $association->id]);
    }
}
