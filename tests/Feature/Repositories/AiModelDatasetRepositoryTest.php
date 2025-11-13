<?php

namespace Tests\Feature\Repositories;

use App\Enums\AiModelDataset\EligibilityStatus;
use App\Enums\AiModelDataset\Role;
use App\Models\AiModel;
use App\Models\AiModelDataset;
use App\Models\AiModelVersion;
use App\Models\DatasetSnapshot;
use App\Models\Organization;
use App\Repositories\AiModelDatasetRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiModelDatasetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AiModelDatasetRepository $repository;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AiModelDatasetRepository();
        $this->organization = Organization::factory()->create();
    }

    private function createAiModelDataset(array $overrides = []): AiModelDataset
    {
        $aiModel = AiModel::factory()->create(['organization_id' => $this->organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'organization_id' => $this->organization->id,
        ]);
        $snapshot = DatasetSnapshot::factory()->create(['organization_id' => $this->organization->id]);

        $data = array_merge([
            'organization_id' => $this->organization->id,
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TRAIN->value,
            'access_path' => '/data/training/path',
            'eligibility_status' => EligibilityStatus::ELIGIBLE->value,
        ], $overrides);

        return AiModelDataset::create($data);
    }

    public function test_get_paginated_ai_model_datasets_returns_paginated_results(): void
    {
        // Create 25 dataset links
        for ($i = 0; $i < 25; $i++) {
            $this->createAiModelDataset();
        }

        $result = $this->repository->getFilteredAiModelDatasets([
            'organization_id' => $this->organization->id,
            'per_page' => 10,
        ]);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertCount(10, $result->items());
    }

    public function test_get_paginated_ai_model_datasets_with_custom_per_page(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->createAiModelDataset();
        }

        $result = $this->repository->getFilteredAiModelDatasets([
            'organization_id' => $this->organization->id,
            'per_page' => 5,
        ]);

        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(20, $result->total());
        $this->assertCount(5, $result->items());
    }

    public function test_create_ai_model_dataset_with_all_fields(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $snapshot = DatasetSnapshot::factory()->create();

        $data = [
            'organization_id' => $this->organization->id,
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TRAIN->value,
            'access_path' => '/data/training/path',
            'transform_pack_link' => 'https://transforms.example.com/pack123',
            'license_check_ref' => 'LIC-123456',
            'privacy_check_ref' => 'PRI-789012',
            'eligibility_status' => EligibilityStatus::ELIGIBLE->value,
            'notes' => 'Test dataset assignment',
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals($aiModel->id, $result->ai_model_id);
        $this->assertEquals($aiModelVersion->id, $result->ai_model_version_id);
        $this->assertEquals($snapshot->id, $result->dataset_snapshot_id);
        $this->assertEquals(Role::TRAIN->value, $result->role);
        $this->assertEquals('/data/training/path', $result->access_path);
        $this->assertEquals('https://transforms.example.com/pack123', $result->transform_pack_link);
        $this->assertEquals('LIC-123456', $result->license_check_ref);
        $this->assertEquals('PRI-789012', $result->privacy_check_ref);
        $this->assertEquals(EligibilityStatus::ELIGIBLE->value, $result->eligibility_status);
        $this->assertEquals('Test dataset assignment', $result->notes);

        $this->assertDatabaseHas('ai_model_dataset', [
            'ai_model_id' => $aiModel->id,
            'role' => Role::TRAIN->value,
        ]);
    }

    public function test_create_ai_model_dataset_with_minimal_fields(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        $data = [
            'organization_id' => $this->organization->id,
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'role' => Role::PRETRAIN->value,
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals($aiModel->id, $result->ai_model_id);
        $this->assertEquals(Role::PRETRAIN->value, $result->role);
        $this->assertNull($result->dataset_snapshot_id);
        $this->assertNull($result->access_path);
    }

    public function test_update_ai_model_dataset(): void
    {
        $aiModelDataset = $this->createAiModelDataset([
            'access_path' => '/original/path',
            'notes' => 'Original notes',
            'eligibility_status' => EligibilityStatus::ELIGIBLE->value,
        ]);

        $updateData = [
            'access_path' => '/updated/path',
            'notes' => 'Updated notes',
            'eligibility_status' => EligibilityStatus::ELIGIBLE_WITH_CONDITIONS->value,
        ];

        $result = $this->repository->update($aiModelDataset, $updateData);

        $this->assertTrue($result);

        $aiModelDataset->refresh();

        $this->assertEquals('/updated/path', $aiModelDataset->access_path);
        $this->assertEquals('Updated notes', $aiModelDataset->notes);
        $this->assertEquals(EligibilityStatus::ELIGIBLE_WITH_CONDITIONS->value, $aiModelDataset->eligibility_status);

        $this->assertDatabaseHas('ai_model_dataset', [
            'id' => $aiModelDataset->id,
            'access_path' => '/updated/path',
            'notes' => 'Updated notes',
        ]);
    }

    public function test_update_ai_model_dataset_partial(): void
    {
        $aiModelDataset = $this->createAiModelDataset([
            'access_path' => '/original/path',
            'notes' => 'Original notes',
        ]);

        // Only update notes
        $updateData = [
            'notes' => 'Only notes updated',
        ];

        $result = $this->repository->update($aiModelDataset, $updateData);

        $this->assertTrue($result);

        $aiModelDataset->refresh();

        $this->assertEquals('Only notes updated', $aiModelDataset->notes);
        $this->assertEquals('/original/path', $aiModelDataset->access_path); // Unchanged
    }

    public function test_update_ai_model_dataset_role(): void
    {
        $aiModelDataset = $this->createAiModelDataset([
            'role' => Role::TRAIN->value,
        ]);

        $updateData = [
            'role' => Role::VALIDATION->value,
        ];

        $result = $this->repository->update($aiModelDataset, $updateData);

        $this->assertTrue($result);

        $aiModelDataset->refresh();

        $this->assertEquals(Role::VALIDATION->value, $aiModelDataset->role);
    }

    public function test_create_dataset_link_with_different_roles(): void
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
            ];

            // Add snapshot for roles that typically require it
            if (in_array($role, [Role::TRAIN, Role::VALIDATION, Role::TEST, Role::EVAL_BENCHMARK])) {
                $data['dataset_snapshot_id'] = $snapshot->id;
            }

            $result = $this->repository->create($data);

            $this->assertInstanceOf(AiModelDataset::class, $result);
            $this->assertEquals($role->value, $result->role);
        }
    }

    public function test_create_dataset_link_with_different_eligibility_statuses(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $snapshot = DatasetSnapshot::factory()->create();

        foreach (EligibilityStatus::cases() as $status) {
            $data = [
                'organization_id' => $this->organization->id,
                'ai_model_id' => $aiModel->id,
                'ai_model_version_id' => $aiModelVersion->id,
                'dataset_snapshot_id' => $snapshot->id,
                'role' => Role::TRAIN->value,
                'eligibility_status' => $status->value,
            ];

            $result = $this->repository->create($data);

            $this->assertInstanceOf(AiModelDataset::class, $result);
            $this->assertEquals($status->value, $result->eligibility_status);
        }
    }

    public function test_update_can_change_eligibility_status(): void
    {
        $aiModelDataset = $this->createAiModelDataset([
            'eligibility_status' => EligibilityStatus::ELIGIBLE->value,
        ]);

        $updateData = [
            'eligibility_status' => EligibilityStatus::NOT_ELIGIBLE->value,
            'notes' => 'Changed to not eligible due to license restrictions',
        ];

        $result = $this->repository->update($aiModelDataset, $updateData);

        $this->assertTrue($result);

        $aiModelDataset->refresh();

        $this->assertEquals(EligibilityStatus::NOT_ELIGIBLE->value, $aiModelDataset->eligibility_status);
        $this->assertEquals('Changed to not eligible due to license restrictions', $aiModelDataset->notes);
    }

    public function test_update_returns_false_on_failure(): void
    {
        $aiModelDataset = $this->createAiModelDataset();

        // Mock a scenario where update fails
        $aiModelDataset->exists = false;

        $result = $this->repository->update($aiModelDataset, ['notes' => 'Should fail']);

        $this->assertFalse($result);
    }
}
