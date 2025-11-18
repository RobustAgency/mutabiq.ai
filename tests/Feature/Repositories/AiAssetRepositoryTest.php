<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\AiAsset;
use App\Models\Agreement;
use App\Models\Organization;
use App\Repositories\AiAssetRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiAssetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AiAssetRepository $repository;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AiAssetRepository;
        $this->organization = Organization::factory()->create();
    }

    public function test_get_paginated_ai_assets_returns_paginated_collection(): void
    {
        AiAsset::factory()->count(15)->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getPaginatedAiAssets($this->organization->id, 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_get_paginated_ai_assets_with_custom_per_page(): void
    {
        AiAsset::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getPaginatedAiAssets($this->organization->id, 5);

        $this->assertCount(5, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_get_paginated_ai_assets_eager_loads_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        AiAsset::factory()->create([
            'vendor_id' => $vendor->id,
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getPaginatedAiAssets($this->organization->id, 10);

        $this->assertTrue($result->items()[0]->relationLoaded('vendor'));
    }

    public function test_get_paginated_ai_assets_eager_loads_vendor_agreement(): void
    {
        $vendor = Vendor::factory()->create();
        $agreement = Agreement::factory()->create(['vendor_id' => $vendor->id]);
        AiAsset::factory()->create([
            'vendor_id' => $vendor->id,
            'vendor_agreement_id' => $agreement->id,
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getPaginatedAiAssets($this->organization->id, 10);

        $this->assertTrue($result->items()[0]->relationLoaded('vendorAgreement'));
    }

    public function test_create_ai_asset_creates_new_record(): void
    {
        $vendor = Vendor::factory()->create();
        $data = [
            'vendor_id' => $vendor->id,
            'vendor_effective_from' => now()->subMonth(),
            'vendor_effective_to' => now()->addYear(),
            'vendor_agreement_id' => null,
            'vendor_assessment_id' => 123,
            'organization_id' => $this->organization->id,
        ];

        $aiAsset = $this->repository->createAiAsset($data);

        $this->assertInstanceOf(AiAsset::class, $aiAsset);
        $this->assertEquals($vendor->id, $aiAsset->vendor_id);
        $this->assertDatabaseHas('ai_assets', [
            'id' => $aiAsset->id,
            'vendor_id' => $vendor->id,
            'vendor_assessment_id' => 123,
        ]);
    }

    public function test_create_ai_asset_with_vendor_agreement(): void
    {
        $vendor = Vendor::factory()->create();
        $agreement = Agreement::factory()->create(['vendor_id' => $vendor->id]);

        $data = [
            'vendor_id' => $vendor->id,
            'vendor_effective_from' => now()->subMonth(),
            'vendor_effective_to' => now()->addYear(),
            'vendor_agreement_id' => $agreement->id,
            'vendor_assessment_id' => null,
            'organization_id' => $this->organization->id,
        ];

        $aiAsset = $this->repository->createAiAsset($data);

        $this->assertEquals($agreement->id, $aiAsset->vendor_agreement_id);
        $this->assertDatabaseHas('ai_assets', [
            'id' => $aiAsset->id,
            'vendor_agreement_id' => $agreement->id,
        ]);
    }

    public function test_update_ai_asset_updates_existing_record(): void
    {
        $aiAsset = AiAsset::factory()->create([
            'vendor_id' => null,
        ]);

        $vendor = Vendor::factory()->create();
        $updateData = [
            'vendor_id' => $vendor->id,
            'vendor_effective_from' => now()->subWeek(),
            'vendor_effective_to' => now()->addMonths(6),
        ];

        $updated = $this->repository->updateAiAsset($aiAsset, $updateData);

        $this->assertInstanceOf(AiAsset::class, $updated);
        $this->assertEquals($vendor->id, $updated->vendor_id);
        $this->assertDatabaseHas('ai_assets', [
            'id' => $aiAsset->id,
            'vendor_id' => $vendor->id,
        ]);
    }

    public function test_update_ai_asset_can_remove_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        $aiAsset = AiAsset::factory()->create([
            'vendor_id' => $vendor->id,
            'vendor_effective_from' => now()->subMonth(),
            'vendor_effective_to' => now()->addYear(),
        ]);

        $updateData = [
            'vendor_id' => null,
            'vendor_effective_from' => null,
            'vendor_effective_to' => null,
            'vendor_agreement_id' => null,
            'vendor_assessment_id' => null,
        ];

        $updated = $this->repository->updateAiAsset($aiAsset, $updateData);

        $this->assertNull($updated->vendor_id);
        $this->assertNull($updated->vendor_effective_from);
        $this->assertDatabaseHas('ai_assets', [
            'id' => $aiAsset->id,
            'vendor_id' => null,
        ]);
    }

    public function test_update_ai_asset_can_change_vendor_agreement(): void
    {
        $vendor = Vendor::factory()->create();
        $agreement1 = Agreement::factory()->create(['vendor_id' => $vendor->id]);
        $agreement2 = Agreement::factory()->create(['vendor_id' => $vendor->id]);

        $aiAsset = AiAsset::factory()->create([
            'vendor_id' => $vendor->id,
            'vendor_agreement_id' => $agreement1->id,
        ]);

        $updateData = ['vendor_agreement_id' => $agreement2->id];

        $updated = $this->repository->updateAiAsset($aiAsset, $updateData);

        $this->assertEquals($agreement2->id, $updated->vendor_agreement_id);
        $this->assertDatabaseHas('ai_assets', [
            'id' => $aiAsset->id,
            'vendor_agreement_id' => $agreement2->id,
        ]);
    }

    public function test_update_ai_asset_partial_update(): void
    {
        $vendor = Vendor::factory()->create();
        $aiAsset = AiAsset::factory()->create([
            'vendor_id' => $vendor->id,
            'vendor_assessment_id' => 100,
        ]);

        $updateData = ['vendor_assessment_id' => 999];

        $updated = $this->repository->updateAiAsset($aiAsset, $updateData);

        $this->assertEquals($vendor->id, $updated->vendor_id);
        $this->assertEquals(999, $updated->vendor_assessment_id);
    }

    public function test_delete_ai_asset_removes_record(): void
    {
        $aiAsset = AiAsset::factory()->create();

        $result = $this->repository->deleteAiAsset($aiAsset);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('ai_assets', [
            'id' => $aiAsset->id,
        ]);
    }

    public function test_delete_ai_asset_returns_true_on_success(): void
    {
        $aiAsset = AiAsset::factory()->create();

        $result = $this->repository->deleteAiAsset($aiAsset);

        $this->assertTrue($result);
    }

    public function test_delete_ai_asset_with_vendor_relationship(): void
    {
        $vendor = Vendor::factory()->create();
        $aiAsset = AiAsset::factory()->create(['vendor_id' => $vendor->id]);

        $result = $this->repository->deleteAiAsset($aiAsset);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('ai_assets', ['id' => $aiAsset->id]);
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);
    }

    public function test_delete_ai_asset_with_agreement_relationship(): void
    {
        $vendor = Vendor::factory()->create();
        $agreement = Agreement::factory()->create(['vendor_id' => $vendor->id]);
        $aiAsset = AiAsset::factory()->create([
            'vendor_id' => $vendor->id,
            'vendor_agreement_id' => $agreement->id,
        ]);

        $result = $this->repository->deleteAiAsset($aiAsset);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('ai_assets', ['id' => $aiAsset->id]);
        $this->assertDatabaseHas('agreements', ['id' => $agreement->id]);
    }

    public function test_get_paginated_ai_assets_handles_empty_results(): void
    {
        $result = $this->repository->getPaginatedAiAssets(10);

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }
}
