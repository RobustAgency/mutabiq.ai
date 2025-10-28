<?php

namespace Tests\Feature\Repositories;

use App\Models\Stakeholder;
use App\Models\Vendor;
use App\Repositories\VendorRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private VendorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new VendorRepository();
    }

    /**
     * Test get paginated vendors returns paginated results.
     */
    public function test_get_paginated_vendors_returns_paginated_results(): void
    {
        Vendor::factory()->count(20)->create();

        $result = $this->repository->getPaginatedVendors(10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /**
     * Test get paginated vendors uses default per page value.
     */
    public function test_get_paginated_vendors_uses_default_per_page(): void
    {
        Vendor::factory()->count(20)->create();

        $result = $this->repository->getPaginatedVendors();

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get paginated vendors orders by created_at descending.
     */
    public function test_get_paginated_vendors_orders_by_created_at_desc(): void
    {
        $oldVendor = Vendor::factory()->create([
            'created_at' => now()->subDays(10),
        ]);
        $newVendor = Vendor::factory()->create([
            'created_at' => now(),
        ]);

        $result = $this->repository->getPaginatedVendors();

        $this->assertEquals($newVendor->id, $result->items()[0]->id);
        $this->assertEquals($oldVendor->id, $result->items()[1]->id);
    }

    /**
     * Test get paginated vendors returns empty when no records.
     */
    public function test_get_paginated_vendors_returns_empty_when_no_records(): void
    {
        $result = $this->repository->getPaginatedVendors();

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test get paginated vendors eager loads owner stakeholder.
     */
    public function test_get_paginated_vendors_eager_loads_owner_stakeholder(): void
    {
        Vendor::factory()->count(3)->create();

        $result = $this->repository->getPaginatedVendors();

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
            'vendor_name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Inc.',
            'hq_country' => 'US',
            'risk_tier' => 'tier_1',
            'status' => 'approved',
            'stakeholder_id' => $stakeholder->id,
            'primary_contacts' => [
                ['name' => 'John Doe', 'email' => 'john@test.com', 'role' => 'Account Manager', 'primary' => true]
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
        $this->assertEquals($data['stakeholder_id'], $vendor->stakeholder_id);
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
            'vendor_name' => 'Minimal Vendor',
            'legal_name' => 'Minimal Vendor LLC',
            'hq_country' => 'GB',
            'risk_tier' => 'tier_2',
            'status' => 'evaluating',
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
            'vendor_name' => 'JSON Test Vendor',
            'legal_name' => 'JSON Test Vendor Inc.',
            'hq_country' => 'US',
            'risk_tier' => 'tier_3',
            'status' => 'approved',
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
            'stakeholder_id' => $newStakeholder->id,
            'primary_contacts' => [
                ['name' => 'New Contact', 'email' => 'new@test.com', 'primary' => true]
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
        $this->assertEquals($updateData['stakeholder_id'], $updatedVendor->stakeholder_id);
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
            'vendor_name' => 'Updated Name'
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
            'status' => 'approved'
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
                ['name' => 'Old Contact', 'email' => 'old@test.com', 'primary' => true]
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
}
