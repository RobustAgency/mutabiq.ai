<?php

namespace Tests\Unit\Repositories;

use App\Models\Dataset;
use App\Models\Organization;
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
        $organization = Organization::factory()->create();
        Dataset::factory()->count(25)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDatasets([
            'organization_id' => $organization->id,
            'per_page' => 10,
        ]);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertCount(10, $result->items());
    }

    public function test_get_paginated_datasets_uses_default_per_page(): void
    {
        $organization = Organization::factory()->create();
        Dataset::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredDatasets([
            'organization_id' => $organization->id,
        ]);

        $this->assertEquals(15, $result->perPage());
    }

    public function test_get_dataset_by_id_returns_dataset(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create(['name' => 'Test Dataset', 'organization_id' => $organization->id]);

        $result = $this->repository->getDatasetByID($dataset->id);

        $this->assertNotNull($result);
        $this->assertEquals('Test Dataset', $result->name);
        $this->assertEquals($dataset->id, $result->id);
    }

    public function test_create_dataset(): void
    {
        $organization = Organization::factory()->create();
        $data = [
            'organization_id' => $organization->id,
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
        $organization = Organization::factory()->create();
        $data = [
            'organization_id' => $organization->id,
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

    public function test_filter_by_name(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Training Data',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Employee Records',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Analytics Data',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'Customer',
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $dataset) {
            $this->assertStringContainsString('Customer', $dataset->name);
        }
    }

    public function test_filter_by_name_is_case_insensitive(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'PRODUCTION Dataset',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'development dataset',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Production Data',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'production',
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_filter_by_sensitivity(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'sensitivity' => 'high',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'sensitivity' => 'low',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'sensitivity' => 'high',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'sensitivity' => 'medium',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'sensitivity' => 'high',
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $dataset) {
            $this->assertEquals('high', $dataset->sensitivity);
        }
    }

    public function test_filter_by_contains_pii(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'contains_pii' => 'yes',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'contains_pii' => 'no',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'contains_pii' => 'yes',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'contains_pii' => 'yes',
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $dataset) {
            $this->assertEquals('yes', $dataset->contains_pii);
        }
    }

    public function test_filter_by_controller_role(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'controller_role' => 'controller',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'controller_role' => 'processor',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'controller_role' => 'controller',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'controller_role' => 'joint_controller',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'controller_role' => 'controller',
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $dataset) {
            $this->assertEquals('controller', $dataset->controller_role);
        }
    }

    public function test_filter_by_multiple_filters(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Training Data',
            'sensitivity' => 'high',
            'contains_pii' => 'yes',
            'controller_role' => 'controller',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Analytics Data',
            'sensitivity' => 'low',
            'contains_pii' => 'yes',
            'controller_role' => 'controller',
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Production Data',
            'sensitivity' => 'high',
            'contains_pii' => 'no',
            'controller_role' => 'processor',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'Customer',
            'sensitivity' => 'high',
            'contains_pii' => 'yes',
            'controller_role' => 'controller',
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(1, $result->items());
        $dataset = $result->items()[0];
        $this->assertStringContainsString('Customer', $dataset->name);
        $this->assertEquals('high', $dataset->sensitivity);
        $this->assertEquals('yes', $dataset->contains_pii);
        $this->assertEquals('controller', $dataset->controller_role);
    }

    public function test_filter_by_different_sensitivity_levels(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create(['organization_id' => $organization->id, 'sensitivity' => 'low']);
        Dataset::factory()->create(['organization_id' => $organization->id, 'sensitivity' => 'medium']);
        Dataset::factory()->create(['organization_id' => $organization->id, 'sensitivity' => 'high']);
        Dataset::factory()->create(['organization_id' => $organization->id, 'sensitivity' => 'low']);

        $lowResult = $this->repository->getFilteredDatasets(['organization_id' => $organization->id, 'sensitivity' => 'low']);
        $mediumResult = $this->repository->getFilteredDatasets(['organization_id' => $organization->id, 'sensitivity' => 'medium']);
        $highResult = $this->repository->getFilteredDatasets(['organization_id' => $organization->id, 'sensitivity' => 'high']);

        $this->assertCount(2, $lowResult->items());
        $this->assertCount(1, $mediumResult->items());
        $this->assertCount(1, $highResult->items());
    }

    public function test_filter_by_different_controller_roles(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create(['organization_id' => $organization->id, 'controller_role' => 'controller']);
        Dataset::factory()->create(['organization_id' => $organization->id, 'controller_role' => 'processor']);
        Dataset::factory()->create(['organization_id' => $organization->id, 'controller_role' => 'joint_controller']);
        Dataset::factory()->create(['organization_id' => $organization->id, 'controller_role' => 'controller']);

        $controllerResult = $this->repository->getFilteredDatasets(['organization_id' => $organization->id, 'controller_role' => 'controller']);
        $processorResult = $this->repository->getFilteredDatasets(['organization_id' => $organization->id, 'controller_role' => 'processor']);
        $jointResult = $this->repository->getFilteredDatasets(['organization_id' => $organization->id, 'controller_role' => 'joint_controller']);

        $this->assertCount(2, $controllerResult->items());
        $this->assertCount(1, $processorResult->items());
        $this->assertCount(1, $jointResult->items());
    }

    public function test_filter_returns_empty_when_no_matches(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Test Dataset',
            'sensitivity' => 'low',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'NonExistent',
            'sensitivity' => 'high',
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(0, $result->items());
    }

    public function test_filter_with_per_page_parameter(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->count(20)->create([
            'organization_id' => $organization->id,
            'sensitivity' => 'high',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'sensitivity' => 'high',
            'per_page' => 7,
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(7, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(7, $result->perPage());
    }

    public function test_filters_maintain_eager_loading(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->count(3)->create([
            'organization_id' => $organization->id,
            'sensitivity' => 'high',
        ]);

        $filters = ['organization_id' => $organization->id, 'sensitivity' => 'high'];
        $result = $this->repository->getFilteredDatasets($filters);

        foreach ($result->items() as $dataset) {
            $this->assertTrue($dataset->relationLoaded('dataElements'));
        }
    }

    public function test_filter_by_organization_id(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        Dataset::factory()->count(5)->create(['organization_id' => $org1->id]);
        Dataset::factory()->count(3)->create(['organization_id' => $org2->id]);

        $result1 = $this->repository->getFilteredDatasets(['organization_id' => $org1->id]);
        $result2 = $this->repository->getFilteredDatasets(['organization_id' => $org2->id]);

        $this->assertEquals(5, $result1->total());
        $this->assertEquals(3, $result2->total());
    }
}
