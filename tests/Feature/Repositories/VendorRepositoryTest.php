<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Enums\Vendor\RiskTier;
use App\Enums\Vendor\VendorStatus;
use App\Repositories\VendorRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VendorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private VendorRepository $repository;

    private $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new VendorRepository;
        $this->organization = Organization::factory()->create();
    }

    /**
     * Test get paginated vendors returns paginated results.
     */
    public function test_get_paginated_vendors_returns_paginated_results(): void
    {
        Vendor::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getFilteredVendors(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /**
     * Test get paginated vendors uses default per page value.
     */
    public function test_get_paginated_vendors_uses_default_per_page(): void
    {
        Vendor::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getFilteredVendors();

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get paginated vendors returns empty when no records.
     */
    public function test_get_filtered_vendors_returns_empty_when_no_records(): void
    {
        $result = $this->repository->getFilteredVendors();

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test get paginated vendors eager loads owner stakeholder.
     */
    public function test_get_paginated_vendors_eager_loads_owner_stakeholder(): void
    {
        Vendor::factory()->count(3)->create();

        $result = $this->repository->getFilteredVendors();

        foreach ($result->items() as $vendor) {
            $this->assertTrue($vendor->relationLoaded('stakeholder'));
        }
    }

    /**
     * Test create vendor creates new record with all fields.
     */
    public function test_create_vendor_creates_new_record_with_all_fields(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'vendor_name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Inc.',
            'hq_country' => 'US',
            'risk_tier' => 'tier_1',
            'status' => 'approved',
            'type' => ['software_provider', 'cloud_services'],
            'data_processing_role' => 'processor',
            'primary_contacts' => [
                ['name' => 'John Doe', 'email' => 'john@test.com', 'role' => 'Account Manager', 'primary' => true],
            ],
            'metadata' => ['website' => 'https://test.com'],
            'notes' => 'Test notes',
        ];

        $vendor = $this->repository->createVendor($data);

        $this->assertInstanceOf(Vendor::class, $vendor);
        $this->assertEquals($data['vendor_name'], $vendor->vendor_name);
        $this->assertEquals($data['legal_name'], $vendor->legal_name);
        $this->assertEquals($data['hq_country'], $vendor->hq_country);
        $this->assertEquals($data['risk_tier'], $vendor->risk_tier);
        $this->assertEquals($data['status'], $vendor->status);
        $this->assertEquals($data['type'], $vendor->type);
        $this->assertEquals($data['data_processing_role'], $vendor->data_processing_role);
        $this->assertEquals($data['primary_contacts'], $vendor->primary_contacts);
        $this->assertEquals($data['metadata'], $vendor->metadata);
        $this->assertEquals($data['notes'], $vendor->notes);
    }

    /**
     * Test create vendor creates record with only required fields.
     */
    public function test_create_vendor_creates_record_with_only_required_fields(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $data = [
            'organization_id' => $this->organization->id,
            'vendor_name' => 'Minimal Vendor',
            'legal_name' => 'Minimal Vendor LLC',
            'hq_country' => 'GB',
            'risk_tier' => 'tier_2',
            'status' => 'evaluating',
            'type' => ['software_provider'],
            'data_processing_role' => 'controller',
            'stakeholder_id' => $stakeholder->id,
        ];

        $vendor = $this->repository->createVendor($data);

        $this->assertInstanceOf(Vendor::class, $vendor);
        $this->assertEquals($data['vendor_name'], $vendor->vendor_name);
        $this->assertNull($vendor->primary_contacts);
        $this->assertNull($vendor->metadata);
        $this->assertNull($vendor->notes);
    }

    /**
     * Test create vendor stores json fields correctly.
     */
    public function test_create_vendor_stores_json_fields_correctly(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $contacts = [
            ['name' => 'Primary Contact', 'email' => 'primary@vendor.com', 'primary' => true],
            ['name' => 'Secondary Contact', 'email' => 'secondary@vendor.com', 'primary' => false],
        ];
        $metadata = [
            'website' => 'https://vendor.com',
            'sub_processors_url' => 'https://vendor.com/sub-processors',
            'residency_options' => ['US', 'EU'],
        ];

        $data = [
            'organization_id' => $this->organization->id,
            'vendor_name' => 'JSON Test Vendor',
            'legal_name' => 'JSON Test Vendor Inc.',
            'hq_country' => 'US',
            'risk_tier' => 'tier_3',
            'status' => 'approved',
            'type' => ['software_provider', 'saas_provider'],
            'data_processing_role' => 'processor',
            'stakeholder_id' => $stakeholder->id,
            'primary_contacts' => $contacts,
            'metadata' => $metadata,
        ];

        $vendor = $this->repository->createVendor($data);

        $this->assertEquals($contacts, $vendor->primary_contacts);
        $this->assertEquals($metadata, $vendor->metadata);
        $this->assertIsArray($vendor->primary_contacts);
        $this->assertIsArray($vendor->metadata);
    }

    /**
     * Test update vendor updates all fields.
     */
    public function test_update_vendor_updates_all_fields(): void
    {
        $vendor = Vendor::factory()->create();
        $newStakeholder = Stakeholder::factory()->create();

        $updateData = [
            'vendor_name' => 'Updated Vendor Name',
            'legal_name' => 'Updated Legal Name',
            'hq_country' => 'CA',
            'risk_tier' => 'tier_4',
            'status' => 'suspended',
            'type' => ['consulting_services', 'software_provider'],
            'data_processing_role' => 'controller',
            'primary_contacts' => [
                ['name' => 'New Contact', 'email' => 'new@test.com', 'primary' => true],
            ],
            'metadata' => ['updated' => true],
            'notes' => 'Updated notes',
        ];

        $updatedVendor = $this->repository->updateVendor($vendor, $updateData);

        $this->assertEquals($updateData['vendor_name'], $updatedVendor->vendor_name);
        $this->assertEquals($updateData['legal_name'], $updatedVendor->legal_name);
        $this->assertEquals($updateData['hq_country'], $updatedVendor->hq_country);
        $this->assertEquals($updateData['risk_tier'], $updatedVendor->risk_tier);
        $this->assertEquals($updateData['status'], $updatedVendor->status);
        $this->assertEquals($updateData['type'], $updatedVendor->type);
        $this->assertEquals($updateData['data_processing_role'], $updatedVendor->data_processing_role);
        $this->assertEquals($updateData['primary_contacts'], $updatedVendor->primary_contacts);
        $this->assertEquals($updateData['metadata'], $updatedVendor->metadata);
        $this->assertEquals($updateData['notes'], $updatedVendor->notes);
    }

    /**
     * Test update vendor returns fresh instance.
     */
    public function test_update_vendor_returns_fresh_instance(): void
    {
        $vendor = Vendor::factory()->create(['vendor_name' => 'Original Name']);

        $updatedVendor = $this->repository->updateVendor($vendor, [
            'vendor_name' => 'Updated Name',
        ]);

        $this->assertNotSame($vendor, $updatedVendor);
        $this->assertEquals('Updated Name', $updatedVendor->vendor_name);
        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'vendor_name' => 'Updated Name',
        ]);
    }

    /**
     * Test update vendor can update partial fields.
     */
    public function test_update_vendor_can_update_partial_fields(): void
    {
        $vendor = Vendor::factory()->create([
            'vendor_name' => 'Original Name',
            'legal_name' => 'Original Legal Name',
            'status' => 'evaluating',
        ]);

        $updatedVendor = $this->repository->updateVendor($vendor, [
            'status' => 'approved',
        ]);

        $this->assertEquals('Original Name', $updatedVendor->vendor_name);
        $this->assertEquals('Original Legal Name', $updatedVendor->legal_name);
        $this->assertEquals('approved', $updatedVendor->status);
    }

    /**
     * Test update vendor can update json fields.
     */
    public function test_update_vendor_can_update_json_fields(): void
    {
        $vendor = Vendor::factory()->create([
            'primary_contacts' => [
                ['name' => 'Old Contact', 'email' => 'old@test.com', 'primary' => true],
            ],
            'metadata' => ['old' => 'data'],
        ]);

        $newContacts = [
            ['name' => 'New Contact', 'email' => 'new@test.com', 'primary' => true],
            ['name' => 'Another Contact', 'email' => 'another@test.com', 'primary' => false],
        ];
        $newMetadata = ['new' => 'data', 'additional' => 'info'];

        $updatedVendor = $this->repository->updateVendor($vendor, [
            'primary_contacts' => $newContacts,
            'metadata' => $newMetadata,
        ]);

        $this->assertEquals($newContacts, $updatedVendor->primary_contacts);
        $this->assertEquals($newMetadata, $updatedVendor->metadata);
    }

    /**
     * Test delete vendor removes record from database.
     */
    public function test_delete_vendor_removes_record_from_database(): void
    {
        $vendor = Vendor::factory()->create();
        $vendorId = $vendor->id;

        $result = $this->repository->deleteVendor($vendor);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('vendors', [
            'id' => $vendorId,
        ]);
    }

    /**
     * Test delete vendor returns true on success.
     */
    public function test_delete_vendor_returns_true_on_success(): void
    {
        $vendor = Vendor::factory()->create();

        $result = $this->repository->deleteVendor($vendor);

        $this->assertTrue($result);
    }

    /**
     * Test filter by organization id.
     */
    public function test_filter_by_organization_id(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        Vendor::factory()->count(5)->create(['organization_id' => $org1->id]);
        Vendor::factory()->count(3)->create(['organization_id' => $org2->id]);

        $result1 = $this->repository->getFilteredVendors(['organization_id' => $org1->id]);
        $result2 = $this->repository->getFilteredVendors(['organization_id' => $org2->id]);

        $this->assertEquals(5, $result1->total());
        $this->assertEquals(3, $result2->total());
    }

    /**
     * Test filter by risk tier.
     */
    public function test_filter_by_risk_tier(): void
    {
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => 'tier_1',
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => 'tier_2',
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => 'tier_1',
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => 'tier_3',
        ]);

        $filters = ['organization_id' => $this->organization->id, 'risk_tier' => 'tier_1'];
        $result = $this->repository->getFilteredVendors($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $vendor) {
            $this->assertEquals('tier_1', $vendor->risk_tier);
        }
    }

    /**
     * Test filter by status.
     */
    public function test_filter_by_status(): void
    {
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'approved',
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'evaluating',
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'approved',
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'suspended',
        ]);

        $filters = ['organization_id' => $this->organization->id, 'status' => 'approved'];
        $result = $this->repository->getFilteredVendors($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $vendor) {
            $this->assertEquals('approved', $vendor->status);
        }
    }

    /**
     * Test filter by date range.
     */
    public function test_filter_by_date_range(): void
    {
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(10),
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(5),
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'from' => now()->subDays(7)->format('Y-m-d'),
            'to' => now()->subDays(2)->format('Y-m-d'),
        ];
        $result = $this->repository->getFilteredVendors($filters);

        $this->assertCount(1, $result->items());
    }

    /**
     * Test filter by from date only.
     */
    public function test_filter_by_from_date_only(): void
    {
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(10),
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(5),
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'from' => now()->subDays(6)->format('Y-m-d'),
        ];
        $result = $this->repository->getFilteredVendors($filters);

        $this->assertCount(2, $result->items());
    }

    /**
     * Test filter by to date only.
     */
    public function test_filter_by_to_date_only(): void
    {
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(10),
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(5),
        ]);
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'to' => now()->subDays(6)->format('Y-m-d'),
        ];
        $result = $this->repository->getFilteredVendors($filters);

        $this->assertCount(1, $result->items());
    }

    /**
     * Test filter by different risk tiers.
     */
    public function test_filter_by_different_risk_tiers(): void
    {
        Vendor::factory()->create(['organization_id' => $this->organization->id, 'risk_tier' => 'tier_1']);
        Vendor::factory()->create(['organization_id' => $this->organization->id, 'risk_tier' => 'tier_2']);
        Vendor::factory()->create(['organization_id' => $this->organization->id, 'risk_tier' => 'tier_3']);
        Vendor::factory()->create(['organization_id' => $this->organization->id, 'risk_tier' => 'tier_4']);

        $tier1Result = $this->repository->getFilteredVendors(['organization_id' => $this->organization->id, 'risk_tier' => 'tier_1']);
        $tier2Result = $this->repository->getFilteredVendors(['organization_id' => $this->organization->id, 'risk_tier' => 'tier_2']);
        $tier3Result = $this->repository->getFilteredVendors(['organization_id' => $this->organization->id, 'risk_tier' => 'tier_3']);
        $tier4Result = $this->repository->getFilteredVendors(['organization_id' => $this->organization->id, 'risk_tier' => 'tier_4']);

        $this->assertCount(1, $tier1Result->items());
        $this->assertCount(1, $tier2Result->items());
        $this->assertCount(1, $tier3Result->items());
        $this->assertCount(1, $tier4Result->items());
    }

    /**
     * Test filter by different statuses.
     */
    public function test_filter_by_different_statuses(): void
    {
        Vendor::factory()->create(['organization_id' => $this->organization->id, 'status' => 'approved']);
        Vendor::factory()->create(['organization_id' => $this->organization->id, 'status' => 'evaluating']);
        Vendor::factory()->create(['organization_id' => $this->organization->id, 'status' => 'suspended']);
        Vendor::factory()->create(['organization_id' => $this->organization->id, 'status' => 'approved']);

        $approvedResult = $this->repository->getFilteredVendors(['organization_id' => $this->organization->id, 'status' => 'approved']);
        $evaluatingResult = $this->repository->getFilteredVendors(['organization_id' => $this->organization->id, 'status' => 'evaluating']);
        $suspendedResult = $this->repository->getFilteredVendors(['organization_id' => $this->organization->id, 'status' => 'suspended']);

        $this->assertCount(2, $approvedResult->items());
        $this->assertCount(1, $evaluatingResult->items());
        $this->assertCount(1, $suspendedResult->items());
    }

    /**
     * Test filter returns empty when no matches.
     */
    public function test_filter_returns_empty_when_no_matches(): void
    {
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => 'tier_1',
            'status' => 'approved',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'risk_tier' => 'tier_4',
            'status' => 'suspended',
        ];
        $result = $this->repository->getFilteredVendors($filters);

        $this->assertCount(0, $result->items());
    }

    /**
     * Test filter with per page parameter.
     */
    public function test_filter_with_per_page_parameter(): void
    {
        Vendor::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
            'status' => 'approved',
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'status' => 'approved',
            'per_page' => 8,
        ];
        $result = $this->repository->getFilteredVendors($filters);

        $this->assertCount(8, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(8, $result->perPage());
    }

    /**
     * Test filters maintain eager loading.
     */
    public function test_filters_maintain_eager_loading(): void
    {
        Vendor::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'status' => 'approved',
        ]);

        $filters = ['organization_id' => $this->organization->id, 'status' => 'approved'];
        $result = $this->repository->getFilteredVendors($filters);

        foreach ($result->items() as $vendor) {
            $this->assertTrue($vendor->relationLoaded('stakeholder'));
        }
    }

    /**
     * Test get statistics returns correct total count.
     */
    public function test_get_statistics_returns_correct_total_count(): void
    {
        Vendor::factory()->count(15)->create(['organization_id' => $this->organization->id]);

        $stats = $this->repository->getStatistics($this->organization->id);

        $this->assertEquals(15, $stats['total_count']);
    }

    /**
     * Test get statistics counts vendors by high risk.
     */
    public function test_get_statistics_counts_vendors_by_high_risk(): void
    {
        Vendor::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => RiskTier::TIER_1->value,
        ]);
        Vendor::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => RiskTier::TIER_2->value,
        ]);
        Vendor::factory()->count(4)->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => RiskTier::TIER_3->value,
        ]);

        $stats = $this->repository->getStatistics($this->organization->id);

        $this->assertEquals(8, $stats['high_risk_count']);
    }

    /**
     * Test get statistics counts vendors by status.
     */
    public function test_get_statistics_counts_vendors_by_status(): void
    {
        Vendor::factory()->count(8)->create([
            'organization_id' => $this->organization->id,
            'status' => VendorStatus::APPROVED->value,
        ]);
        Vendor::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'status' => VendorStatus::EVALUATING->value,
        ]);

        $stats = $this->repository->getStatistics($this->organization->id);

        $this->assertEquals(8, $stats['approved_count']);
        $this->assertEquals(3, $stats['evaluating_count']);
    }

    /**
     * Test get statistics returns all counts.
     */
    public function test_get_statistics_returns_all_counts(): void
    {
        Vendor::factory()->count(10)->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => RiskTier::TIER_1->value,
            'status' => VendorStatus::APPROVED->value,
        ]);
        Vendor::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
            'risk_tier' => RiskTier::TIER_2->value,
            'status' => VendorStatus::EVALUATING->value,
        ]);

        $stats = $this->repository->getStatistics($this->organization->id);

        $this->assertEquals(15, $stats['total_count']);
        $this->assertEquals(15, $stats['high_risk_count']);
        $this->assertEquals(10, $stats['approved_count']);
        $this->assertEquals(5, $stats['evaluating_count']);
    }
}
