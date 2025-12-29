<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Models\Dataset;
use App\Models\Organization;
use App\Enums\Dataset\Status;
use App\Enums\Dataset\Purpose;
use App\Enums\Dataset\SizeUnit;
use App\Enums\Dataset\DataSteward;
use App\Enums\Dataset\LicenseType;
use App\Enums\Dataset\Sensitivity;
use App\Enums\Dataset\PrimaryLanguage;
use App\Repositories\DatasetRepository;
use App\Enums\Dataset\ContainPersonalData;
use App\Enums\Dataset\CrossBorderTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatasetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DatasetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DatasetRepository;
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
            'description' => 'Test dataset description',
            'source_ids' => [1, 2, 3],
            'purpose' => Purpose::AI_ML_TRAINING->value,
            'owner_team' => 'Data Science',
            'data_steward' => DataSteward::DATA_ENGINEER->value,
            'status' => Status::ACTIVE->value,
            'estimated_row_count' => 100000,
            'estimated_size' => 500,
            'size_unit' => SizeUnit::MEGABYTES->value,
            'retention_period' => '7 years',
            'primary_languages' => [PrimaryLanguage::ENGLISH->value],
            'contains_personal_data' => ContainPersonalData::YES->value,
            'sensitivity' => Sensitivity::CONFIDENTIAL->value,
            'cross_border_transfer' => CrossBorderTransfer::ADEQUACY_DECISION->value,
            'license_type' => LicenseType::PROPRIETARY->value,
        ];

        $dataset = $this->repository->createDataset($data);

        $this->assertInstanceOf(Dataset::class, $dataset);
        $this->assertEquals('New Test Dataset', $dataset->name);
        $this->assertEquals('Data Science', $dataset->owner_team);
        $this->assertEquals(Status::ACTIVE->value, $dataset->status);
        $this->assertEquals(ContainPersonalData::YES->value, $dataset->contains_personal_data);
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
            'description' => 'Dataset with array fields',
            'source_ids' => [1, 2, 3],
            'primary_languages' => [PrimaryLanguage::ENGLISH->value, PrimaryLanguage::SPANISH->value],
            'purpose' => Purpose::ANALYTIC_BUSINESS_INTELLIGENCE->value,
            'owner_team' => 'Engineering',
            'data_steward' => DataSteward::DATA_SCIENTIST->value,
            'status' => Status::ACTIVE->value,
            'sensitivity' => Sensitivity::INTERNAL->value,
            'contains_personal_data' => ContainPersonalData::NO->value,
            'cross_border_transfer' => CrossBorderTransfer::NONE->value,
            'license_type' => LicenseType::OPEN_SOURCE->value,
        ];

        $dataset = $this->repository->createDataset($data);

        $this->assertIsArray($dataset->source_ids);
        $this->assertIsArray($dataset->primary_languages);
        $this->assertCount(3, $dataset->source_ids);
        $this->assertCount(2, $dataset->primary_languages);
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
            'sensitivity' => Sensitivity::RESTRICTED->value,
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'sensitivity' => Sensitivity::PUBLIC->value,
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'sensitivity' => Sensitivity::RESTRICTED->value,
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'sensitivity' => Sensitivity::INTERNAL->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'sensitivity' => Sensitivity::RESTRICTED->value,
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $dataset) {
            $this->assertEquals(Sensitivity::RESTRICTED->value, $dataset->sensitivity);
        }
    }

    public function test_filter_by_multiple_filters(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Training Data',
            'sensitivity' => Sensitivity::RESTRICTED->value,
            'contains_personal_data' => ContainPersonalData::YES->value,
            'status' => Status::ACTIVE->value,
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Analytics Data',
            'sensitivity' => Sensitivity::PUBLIC->value,
            'contains_personal_data' => ContainPersonalData::YES->value,
            'status' => Status::ACTIVE->value,
        ]);
        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Production Data',
            'sensitivity' => Sensitivity::RESTRICTED->value,
            'contains_personal_data' => ContainPersonalData::NO->value,
            'status' => Status::DRAFT->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'Customer',
            'sensitivity' => Sensitivity::RESTRICTED->value,
            'contains_personal_data' => ContainPersonalData::YES->value,
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(1, $result->items());
        $dataset = $result->items()[0];
        $this->assertStringContainsString('Customer', $dataset->name);
        $this->assertEquals(Sensitivity::RESTRICTED->value, $dataset->sensitivity);
        $this->assertEquals(ContainPersonalData::YES->value, $dataset->contains_personal_data);
    }

    public function test_filter_by_different_sensitivity_levels(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create(['organization_id' => $organization->id, 'sensitivity' => Sensitivity::PUBLIC->value]);
        Dataset::factory()->create(['organization_id' => $organization->id, 'sensitivity' => Sensitivity::INTERNAL->value]);
        Dataset::factory()->create(['organization_id' => $organization->id, 'sensitivity' => Sensitivity::RESTRICTED->value]);
        Dataset::factory()->create(['organization_id' => $organization->id, 'sensitivity' => Sensitivity::PUBLIC->value]);

        $publicResult = $this->repository->getFilteredDatasets(['organization_id' => $organization->id, 'sensitivity' => Sensitivity::PUBLIC->value]);
        $internalResult = $this->repository->getFilteredDatasets(['organization_id' => $organization->id, 'sensitivity' => Sensitivity::INTERNAL->value]);
        $restrictedResult = $this->repository->getFilteredDatasets(['organization_id' => $organization->id, 'sensitivity' => Sensitivity::RESTRICTED->value]);

        $this->assertCount(2, $publicResult->items());
        $this->assertCount(1, $internalResult->items());
        $this->assertCount(1, $restrictedResult->items());
    }

    public function test_filter_returns_empty_when_no_matches(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Test Dataset',
            'sensitivity' => Sensitivity::PUBLIC->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'NonExistent',
            'sensitivity' => Sensitivity::RESTRICTED->value,
        ];
        $result = $this->repository->getFilteredDatasets($filters);

        $this->assertCount(0, $result->items());
    }

    public function test_filter_with_per_page_parameter(): void
    {
        $organization = Organization::factory()->create();

        Dataset::factory()->count(20)->create([
            'organization_id' => $organization->id,
            'sensitivity' => Sensitivity::RESTRICTED->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'sensitivity' => Sensitivity::RESTRICTED->value,
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
            'sensitivity' => Sensitivity::RESTRICTED->value,
        ]);

        $filters = ['organization_id' => $organization->id, 'sensitivity' => Sensitivity::RESTRICTED->value];
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
