<?php

namespace Tests\Unit\Repositories;

use App\Models\Dataset;
use App\Repositories\DatasetRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatasetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DatasetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DatasetRepository();
    }

    public function test_get_paginated_datasets_returns_paginated_results(): void
    {
        Dataset::factory()->count(25)->create();

        $result = $this->repository->getPaginatedDatasets(10);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertCount(10, $result->items());
    }

    public function test_get_paginated_datasets_uses_default_per_page(): void
    {
        Dataset::factory()->count(20)->create();

        $result = $this->repository->getPaginatedDatasets();

        $this->assertEquals(15, $result->perPage());
    }

    public function test_get_dataset_by_id_returns_dataset(): void
    {
        $dataset = Dataset::factory()->create(['name' => 'Test Dataset']);

        $result = $this->repository->getDatasetByID($dataset->id);

        $this->assertNotNull($result);
        $this->assertEquals('Test Dataset', $result->name);
        $this->assertEquals($dataset->id, $result->id);
    }

    public function test_get_dataset_by_id_returns_null_for_nonexistent_id(): void
    {
        $result = $this->repository->getDatasetByID(99999);

        $this->assertNull($result);
    }

    public function test_create_dataset(): void
    {
        $data = [
            'name' => 'New Test Dataset',
            'source_ids' => [1, 2, 3],
            'purpose' => 'training',
            'sensitivity' => 'high',
            'contains_pii' => 'yes',
            'data_subject_categories' => ['customers', 'employees'],
            'controller_role' => 'controller',
            'lawful_basis' => 'consent',
            'consent_required' => true,
            'cross_border_transfer' => 'none',
            'data_structure' => 'tabular',
            'storage_format' => 'table',
            'owner_team' => 'Data Science',
        ];

        $dataset = $this->repository->createDataset($data);

        $this->assertInstanceOf(Dataset::class, $dataset);
        $this->assertEquals('New Test Dataset', $dataset->name);
        $this->assertEquals('Data Science', $dataset->owner_team);
        $this->assertTrue($dataset->consent_required);
        $this->assertDatabaseHas('datasets', ['name' => 'New Test Dataset']);
    }

    public function test_update_dataset_updates_existing_dataset(): void
    {
        $dataset = Dataset::factory()->create([
            'name' => 'Original Name',
            'owner_team' => 'Original Team',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'owner_team' => 'Updated Team',
        ];

        $result = $this->repository->updateDataset($dataset, $updateData);

        $this->assertTrue($result);
        $dataset->refresh();
        $this->assertEquals('Updated Name', $dataset->name);
        $this->assertEquals('Updated Team', $dataset->owner_team);
    }

    public function test_delete_removes_dataset(): void
    {
        $dataset = Dataset::factory()->create();
        $datasetId = $dataset->id;

        $result = $this->repository->delete($dataset);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('datasets', ['id' => $datasetId]);
    }

    public function test_create_dataset_with_array_fields(): void
    {
        $data = [
            'name' => 'Array Fields Dataset',
            'source_ids' => [1, 2, 3],
            'data_subject_categories' => ['customers', 'employees'],
            'content_types' => ['text', 'image', 'video'],
            'purpose' => 'training',
            'sensitivity' => 'medium',
            'contains_pii' => 'no',
            'controller_role' => 'controller',
            'lawful_basis' => 'legitimate_interest',
            'consent_required' => false,
            'cross_border_transfer' => 'none',
            'data_structure' => 'tabular',
            'storage_format' => 'table',
            'owner_team' => 'Engineering',
        ];

        $dataset = $this->repository->createDataset($data);

        $this->assertIsArray($dataset->source_ids);
        $this->assertIsArray($dataset->data_subject_categories);
        $this->assertIsArray($dataset->content_types);
        $this->assertCount(3, $dataset->source_ids);
        $this->assertCount(2, $dataset->data_subject_categories);
        $this->assertCount(3, $dataset->content_types);
    }
}
