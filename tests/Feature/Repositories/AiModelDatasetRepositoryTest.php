<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\AiModel;
use App\Models\Dataset;
use App\Models\Organization;
use App\Models\AiModelDataset;
use App\Models\AiModelVersion;
use App\Models\DatasetSnapshot;
use App\Enums\AiModelDataset\Role;
use App\Enums\AiModelDataset\CreatedBy;
use App\Enums\AiModelDataset\LinkageStatus;
use App\Enums\AiModelDataset\CrossBorderCheck;
use App\Repositories\AiModelDatasetRepository;
use App\Enums\AiModelDataset\ConsentCheckStatus;
use App\Enums\AiModelDataset\SpecialCategoryCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiModelDatasetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AiModelDatasetRepository $repository;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AiModelDatasetRepository;
        $this->organization = Organization::factory()->create();
    }

    public function test_get_filtered_returns_paginated_results(): void
    {
        AiModelDataset::factory(25)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getFilteredAiModelDatasets([
            'organization_id' => $this->organization->id,
            'per_page' => 10,
        ]);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertCount(10, $result->items());
    }

    public function test_get_filtered_with_default_per_page(): void
    {
        AiModelDataset::factory(20)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getFilteredAiModelDatasets([
            'organization_id' => $this->organization->id,
        ]);

        $this->assertEquals(15, $result->perPage()); // Default per_page
        $this->assertEquals(20, $result->total());
    }

    public function test_get_filtered_eager_loads_relationships(): void
    {
        $record = AiModelDataset::factory()->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getFilteredAiModelDatasets([
            'organization_id' => $this->organization->id,
        ]);

        $this->assertNotNull($result->first()->aiModel);
        $this->assertNotNull($result->first()->aiModelVersion);
        $this->assertNotNull($result->first()->dataset);
    }

    public function test_create_with_all_fields(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();
        $snapshot = DatasetSnapshot::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TRAIN->value,
            'rows_used' => 50000,
            'training_start_date' => '2024-01-01',
            'training_end_date' => '2024-01-15',
            'training_duration' => '24h',
            'compute_resources' => 'GPU-8',
            'cost' => 1234.56,
            'consent_check_status' => ConsentCheckStatus::PASSED->value,
            'cross_border_check' => CrossBorderCheck::PASSED->value,
            'special_category_check' => SpecialCategoryCheck::PASSED->value,
            'bias_mitigation_applied' => true,
            'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
            'linkage_status' => LinkageStatus::APPROVED->value,
            'business_justification' => 'Training dataset for model version 1.0',
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals($aiModel->id, $result->ai_model_id);
        $this->assertEquals($snapshot->id, $result->dataset_snapshot_id);
        $this->assertEquals(Role::TRAIN->value, $result->role);
        $this->assertEquals(50000, $result->rows_used);
        $this->assertEquals(1234.56, $result->cost);
        $this->assertTrue($result->bias_mitigation_applied);

        $this->assertDatabaseHas('ai_model_dataset', [
            'ai_model_id' => $aiModel->id,
            'role' => Role::TRAIN->value,
        ]);
    }

    public function test_create_with_minimal_required_fields(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::PRETRAIN->value,
            'cross_border_check' => CrossBorderCheck::PASSED->value,
            'special_category_check' => SpecialCategoryCheck::NOT_APPLICABLE->value,
            'created_by_system' => CreatedBy::ML_PLATFORM_TEAM->value,
            'linkage_status' => LinkageStatus::PENDING_APPROVAL->value,
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals(Role::PRETRAIN->value, $result->role);
        $this->assertNull($result->rows_used);
        $this->assertNull($result->cost);
    }

    // ========== CRUD: Update Tests ==========

    public function test_update_multiple_fields(): void
    {
        $record = AiModelDataset::factory()->train()->create();

        $updateData = [
            'rows_used' => 75000,
            'cost' => 2000.00,
            'linkage_status' => LinkageStatus::ACTIVE->value,
        ];

        $result = $this->repository->update($record, $updateData);

        $this->assertTrue($result);
        $record->refresh();

        $this->assertEquals(75000, $record->rows_used);
        $this->assertEquals(2000.00, $record->cost);
        $this->assertEquals(LinkageStatus::ACTIVE->value, $record->linkage_status);
    }

    public function test_update_partial_fields(): void
    {
        $record = AiModelDataset::factory()->create([
            'rows_used' => 10000,
            'cost' => 500.00,
        ]);

        $updateData = ['cost' => 600.00];

        $result = $this->repository->update($record, $updateData);

        $this->assertTrue($result);
        $record->refresh();

        $this->assertEquals(10000, $record->rows_used); // Unchanged
        $this->assertEquals(600.00, $record->cost);
    }

    public function test_update_role(): void
    {
        $record = AiModelDataset::factory()->create(['role' => Role::TRAIN->value]);

        $updateData = ['role' => Role::VALIDATION->value];

        $result = $this->repository->update($record, $updateData);

        $this->assertTrue($result);
        $record->refresh();

        $this->assertEquals(Role::VALIDATION->value, $record->role);
    }

    public function test_update_linkage_status(): void
    {
        $record = AiModelDataset::factory()->create([
            'linkage_status' => LinkageStatus::PENDING_APPROVAL->value,
        ]);

        $updateData = ['linkage_status' => LinkageStatus::ARCHIVED->value];

        $result = $this->repository->update($record, $updateData);

        $this->assertTrue($result);
        $record->refresh();

        $this->assertEquals(LinkageStatus::ARCHIVED->value, $record->linkage_status);
    }

    public function test_all_role_enum_values_can_be_stored(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $snapshot = DatasetSnapshot::factory()->create();

        foreach (Role::cases() as $role) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_model_id' => $aiModel->id,
                'ai_model_version_id' => $aiModelVersion->id,
                'role' => $role->value,
                'cross_border_check' => CrossBorderCheck::PASSED->value,
                'special_category_check' => SpecialCategoryCheck::NOT_APPLICABLE->value,
                'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
                'linkage_status' => LinkageStatus::PENDING_APPROVAL->value,
            ];

            // Add snapshot for roles that require it
            if (in_array($role, [Role::TRAIN, Role::VALIDATION, Role::TEST, Role::EVAL_BENCHMARK])) {
                $data['dataset_snapshot_id'] = $snapshot->id;
            }

            $result = $this->repository->create($data);

            $this->assertEquals($role->value, $result->role);
        }
    }

    public function test_all_created_by_enum_values_can_be_stored(): void
    {
        foreach (CreatedBy::cases() as $createdBy) {
            $result = $this->repository->create([
                'organization_id' => $this->organization->id,
                'ai_model_id' => AiModel::factory()->create()->id,
                'ai_model_version_id' => AiModelVersion::factory()->create()->id,
                'dataset_id' => Dataset::factory()->create()->id,
                'role' => Role::PRETRAIN->value,
                'created_by_system' => $createdBy->value,
                'cross_border_check' => CrossBorderCheck::PASSED->value,
                'special_category_check' => SpecialCategoryCheck::NOT_APPLICABLE->value,
                'linkage_status' => LinkageStatus::PENDING_APPROVAL->value,
            ]);

            $this->assertEquals($createdBy->value, $result->created_by_system);
        }
    }

    public function test_all_linkage_status_enum_values_can_be_stored(): void
    {
        foreach (LinkageStatus::cases() as $status) {
            $result = $this->repository->create([
                'organization_id' => $this->organization->id,
                'ai_model_id' => AiModel::factory()->create()->id,
                'ai_model_version_id' => AiModelVersion::factory()->create()->id,
                'dataset_id' => Dataset::factory()->create()->id,
                'role' => Role::PRETRAIN->value,
                'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
                'cross_border_check' => CrossBorderCheck::PASSED->value,
                'special_category_check' => SpecialCategoryCheck::NOT_APPLICABLE->value,
                'linkage_status' => $status->value,
            ]);

            $this->assertEquals($status->value, $result->linkage_status);
        }
    }

    public function test_nullable_integer_fields(): void
    {
        $record = AiModelDataset::factory()->create([
            'rows_used' => null,
        ]);

        $this->assertNull($record->rows_used);

        $updateData = ['rows_used' => 500000];
        $this->repository->update($record, $updateData);
        $record->refresh();

        $this->assertEquals(500000, $record->rows_used);
    }

    public function test_nullable_date_fields(): void
    {
        $record = AiModelDataset::factory()->create([
            'training_start_date' => null,
            'training_end_date' => null,
        ]);

        $this->assertNull($record->training_start_date);
        $this->assertNull($record->training_end_date);

        $updateData = [
            'training_start_date' => '2024-06-01',
            'training_end_date' => '2024-06-30',
        ];
        $this->repository->update($record, $updateData);
        $record->refresh();

        $this->assertEquals('2024-06-01', $record->training_start_date->format('Y-m-d'));
        $this->assertEquals('2024-06-30', $record->training_end_date->format('Y-m-d'));
    }

    public function test_nullable_decimal_fields(): void
    {
        $record = AiModelDataset::factory()->create([
            'cost' => null,
        ]);

        $this->assertNull($record->cost);

        $updateData = ['cost' => 9999.99];
        $this->repository->update($record, $updateData);
        $record->refresh();

        $this->assertEquals(9999.99, $record->cost);
    }

    public function test_boolean_field_persistence(): void
    {
        $record = AiModelDataset::factory()->create([
            'bias_mitigation_applied' => true,
        ]);

        $this->assertTrue($record->bias_mitigation_applied);

        $updateData = ['bias_mitigation_applied' => false];
        $this->repository->update($record, $updateData);
        $record->refresh();

        $this->assertFalse($record->bias_mitigation_applied);
    }

    public function test_nullable_string_fields(): void
    {
        $record = AiModelDataset::factory()->create([
            'training_duration' => null,
            'compute_resources' => null,
            'business_justification' => null,
        ]);

        $this->assertNull($record->training_duration);
        $this->assertNull($record->compute_resources);
        $this->assertNull($record->business_justification);

        $updateData = [
            'training_duration' => '48h',
            'compute_resources' => 'GPU-8',
            'business_justification' => 'Updated justification',
        ];
        $this->repository->update($record, $updateData);
        $record->refresh();

        $this->assertEquals('48h', $record->training_duration);
        $this->assertEquals('GPU-8', $record->compute_resources);
        $this->assertEquals('Updated justification', $record->business_justification);
    }

    // ========== Relationship Tests ==========

    public function test_relationships_are_properly_associated(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $record = $this->repository->create([
            'organization_id' => $this->organization->id,
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::PRETRAIN->value,
            'cross_border_check' => CrossBorderCheck::PASSED->value,
            'special_category_check' => SpecialCategoryCheck::NOT_APPLICABLE->value,
            'created_by_system' => CreatedBy::DATA_ENGINEERING_TEAM->value,
            'linkage_status' => LinkageStatus::PENDING_APPROVAL->value,
        ]);

        $this->assertEquals($aiModel->id, $record->aiModel->id);
        $this->assertEquals($aiModelVersion->id, $record->aiModelVersion->id);
        $this->assertEquals($dataset->id, $record->dataset->id);
    }

    public function test_filter_by_role(): void
    {
        AiModelDataset::factory(5)->create([
            'organization_id' => $this->organization->id,
            'role' => Role::TRAIN->value,
        ]);
        AiModelDataset::factory(3)->create([
            'organization_id' => $this->organization->id,
            'role' => Role::VALIDATION->value,
        ]);

        $result = $this->repository->getFilteredAiModelDatasets([
            'organization_id' => $this->organization->id,
            'role' => Role::TRAIN->value,
        ]);

        $this->assertEquals(5, $result->total());
    }

    public function test_filter_by_date_range(): void
    {
        AiModelDataset::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(10),
        ]);
        AiModelDataset::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(5),
        ]);
        AiModelDataset::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now(),
        ]);

        $result = $this->repository->getFilteredAiModelDatasets([
            'organization_id' => $this->organization->id,
            'from' => now()->subDays(6)->toDateString(),
            'to' => now()->toDateString(),
        ]);

        $this->assertEquals(2, $result->total());
    }
}
