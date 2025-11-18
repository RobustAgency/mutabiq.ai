<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\Agreement;
use App\Models\Organization;
use App\Repositories\AgreementRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgreementRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AgreementRepository $repository;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AgreementRepository;
        $this->organization = Organization::factory()->create();
    }

    /**
     * Test get paginated agreements returns paginated results.
     */
    public function test_get_paginated_agreements_returns_paginated_results(): void
    {
        Agreement::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getPaginatedAgreements($this->organization->id, 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /**
     * Test get paginated agreements uses default per page value.
     */
    public function test_get_paginated_agreements_uses_default_per_page(): void
    {
        Agreement::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getPaginatedAgreements($this->organization->id);

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get paginated agreements orders by created_at descending.
     */
    public function test_get_paginated_agreements_orders_by_created_at_desc(): void
    {
        $oldAgreement = Agreement::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(10),
        ]);
        $newAgreement = Agreement::factory()->create([
            'organization_id' => $this->organization->id,
            'created_at' => now(),
        ]);

        $result = $this->repository->getPaginatedAgreements($this->organization->id);

        $this->assertEquals($newAgreement->id, $result->items()[0]->id);
        $this->assertEquals($oldAgreement->id, $result->items()[1]->id);
    }

    /**
     * Test get paginated agreements returns empty when no records.
     */
    public function test_get_paginated_agreements_returns_empty_when_no_records(): void
    {
        $result = $this->repository->getPaginatedAgreements($this->organization->id);

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test get paginated agreements eager loads vendor.
     */
    public function test_get_paginated_agreements_eager_loads_vendor(): void
    {
        Agreement::factory()->count(3)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getPaginatedAgreements($this->organization->id);

        foreach ($result->items() as $agreement) {
            $this->assertTrue($agreement->relationLoaded('vendor'));
        }
    }

    /**
     * Test create agreement creates new record with all fields.
     */
    public function test_create_agreement_creates_new_record_with_all_fields(): void
    {
        $vendor = Vendor::factory()->create(['organization_id' => $this->organization->id]);
        $data = [
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
            'agreement_type' => 'dpa',
            'status' => 'active',
            'effective_from' => now(),
            'effective_to' => now()->addYear(),
            'training_opt_out' => 'yes',
            'audit_rights' => 'yes',
            'transfer_mechanism' => 'sccs',
            'sla_terms' => [
                'availability_target_pct' => 99.9,
                'latency_p95_ms' => 200,
                'support_tier' => 'premium',
            ],
            'doc_ref' => 'https://example.com/agreement.pdf',
        ];

        $agreement = $this->repository->createAgreement($data);

        $this->assertInstanceOf(Agreement::class, $agreement);
        $this->assertEquals($data['vendor_id'], $agreement->vendor_id);
        $this->assertEquals($data['agreement_type'], $agreement->agreement_type);
        $this->assertEquals($data['status'], $agreement->status);
        $this->assertEquals($data['training_opt_out'], $agreement->training_opt_out);
        $this->assertEquals($data['audit_rights'], $agreement->audit_rights);
        $this->assertEquals($data['transfer_mechanism'], $agreement->transfer_mechanism);
        $this->assertEquals($data['sla_terms'], $agreement->sla_terms);
        $this->assertEquals($data['doc_ref'], $agreement->doc_ref);
    }

    /**
     * Test create agreement creates record with only required fields.
     */
    public function test_create_agreement_creates_record_with_only_required_fields(): void
    {
        $vendor = Vendor::factory()->create(['organization_id' => $this->organization->id]);
        $data = [
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
            'agreement_type' => 'msa',
            'status' => 'draft',
            'effective_from' => now(),
            'effective_to' => now()->addYear(),
            'doc_ref' => 'https://example.com/msa.pdf',
        ];

        $agreement = $this->repository->createAgreement($data);

        $this->assertInstanceOf(Agreement::class, $agreement);
        $this->assertEquals($data['vendor_id'], $agreement->vendor_id);
        $this->assertNull($agreement->training_opt_out);
        $this->assertNull($agreement->audit_rights);
        $this->assertNull($agreement->transfer_mechanism);
        $this->assertNull($agreement->sla_terms);
    }

    /**
     * Test create agreement stores datetime fields correctly.
     */
    public function test_create_agreement_stores_datetime_fields_correctly(): void
    {
        $vendor = Vendor::factory()->create(['organization_id' => $this->organization->id]);
        $effectiveFrom = now()->subMonth();
        $effectiveTo = now()->addYear();

        $data = [
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
            'agreement_type' => 'dpa',
            'status' => 'active',
            'effective_from' => $effectiveFrom,
            'effective_to' => $effectiveTo,
            'doc_ref' => 'https://example.com/dpa.pdf',
        ];

        $agreement = $this->repository->createAgreement($data);

        $this->assertEquals($effectiveFrom->format('Y-m-d H:i:s'), $agreement->effective_from->format('Y-m-d H:i:s'));
        $this->assertEquals($effectiveTo->format('Y-m-d H:i:s'), $agreement->effective_to->format('Y-m-d H:i:s'));
    }

    /**
     * Test create agreement stores sla_terms json correctly.
     */
    public function test_create_agreement_stores_sla_terms_json_correctly(): void
    {
        $vendor = Vendor::factory()->create(['organization_id' => $this->organization->id]);
        $slaTerms = [
            'availability_target_pct' => 99.95,
            'latency_p95_ms' => 150,
            'support_tier' => 'enterprise',
            'breach_definition' => 'Service down for more than 1 hour',
            'credit_schedule_ref' => 'SLA-CREDIT-2024',
            'monitoring_ref' => 'MON-2024',
        ];

        $data = [
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
            'agreement_type' => 'sla',
            'status' => 'active',
            'effective_from' => now(),
            'effective_to' => now()->addYear(),
            'sla_terms' => $slaTerms,
            'doc_ref' => 'https://example.com/sla.pdf',
        ];

        $agreement = $this->repository->createAgreement($data);

        $this->assertEquals($slaTerms, $agreement->sla_terms);
        $this->assertIsArray($agreement->sla_terms);
        $this->assertEquals(99.95, $agreement->sla_terms['availability_target_pct']);
    }

    /**
     * Test update agreement updates all fields.
     */
    public function test_update_agreement_updates_all_fields(): void
    {
        $agreement = Agreement::factory()->create(['organization_id' => $this->organization->id]);
        $newVendor = Vendor::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = [
            'vendor_id' => $newVendor->id,
            'agreement_type' => 'order_form',
            'status' => 'lapsed',
            'effective_from' => now()->subYear(),
            'effective_to' => now(),
            'training_opt_out' => 'no',
            'audit_rights' => 'limited',
            'transfer_mechanism' => 'adequacy',
            'sla_terms' => ['new' => 'terms'],
            'doc_ref' => 'https://updated.com/agreement.pdf',
        ];

        $updatedAgreement = $this->repository->updateAgreement($agreement, $updateData);

        $this->assertEquals($updateData['vendor_id'], $updatedAgreement->vendor_id);
        $this->assertEquals($updateData['agreement_type'], $updatedAgreement->agreement_type);
        $this->assertEquals($updateData['status'], $updatedAgreement->status);
        $this->assertEquals($updateData['training_opt_out'], $updatedAgreement->training_opt_out);
        $this->assertEquals($updateData['audit_rights'], $updatedAgreement->audit_rights);
        $this->assertEquals($updateData['transfer_mechanism'], $updatedAgreement->transfer_mechanism);
        $this->assertEquals($updateData['sla_terms'], $updatedAgreement->sla_terms);
        $this->assertEquals($updateData['doc_ref'], $updatedAgreement->doc_ref);
    }

    /**
     * Test update agreement returns fresh instance.
     */
    public function test_update_agreement_returns_fresh_instance(): void
    {
        $agreement = Agreement::factory()->create(['status' => 'draft']);

        $updatedAgreement = $this->repository->updateAgreement($agreement, [
            'status' => 'active',
        ]);

        $this->assertNotSame($agreement, $updatedAgreement);
        $this->assertEquals('active', $updatedAgreement->status);
        $this->assertDatabaseHas('agreements', [
            'id' => $agreement->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test update agreement can update partial fields.
     */
    public function test_update_agreement_can_update_partial_fields(): void
    {
        $agreement = Agreement::factory()->create([
            'agreement_type' => 'msa',
            'status' => 'draft',
            'doc_ref' => 'https://original.com/doc.pdf',
        ]);

        $updatedAgreement = $this->repository->updateAgreement($agreement, [
            'status' => 'active',
        ]);

        $this->assertEquals('msa', $updatedAgreement->agreement_type);
        $this->assertEquals('active', $updatedAgreement->status);
        $this->assertEquals('https://original.com/doc.pdf', $updatedAgreement->doc_ref);
    }

    /**
     * Test update agreement can update sla_terms.
     */
    public function test_update_agreement_can_update_sla_terms(): void
    {
        $agreement = Agreement::factory()->sla()->create();

        $newSlaTerms = [
            'availability_target_pct' => 99.99,
            'latency_p95_ms' => 100,
            'support_tier' => 'premium',
        ];

        $updatedAgreement = $this->repository->updateAgreement($agreement, [
            'sla_terms' => $newSlaTerms,
        ]);

        $this->assertEquals($newSlaTerms, $updatedAgreement->sla_terms);
    }

    /**
     * Test update agreement can clear optional fields.
     */
    public function test_update_agreement_can_clear_optional_fields(): void
    {
        $agreement = Agreement::factory()->create([
            'training_opt_out' => 'yes',
            'audit_rights' => 'yes',
            'transfer_mechanism' => 'sccs',
        ]);

        $updatedAgreement = $this->repository->updateAgreement($agreement, [
            'training_opt_out' => null,
            'audit_rights' => null,
            'transfer_mechanism' => null,
        ]);

        $this->assertNull($updatedAgreement->training_opt_out);
        $this->assertNull($updatedAgreement->audit_rights);
        $this->assertNull($updatedAgreement->transfer_mechanism);
    }

    /**
     * Test delete agreement removes record from database.
     */
    public function test_delete_agreement_removes_record_from_database(): void
    {
        $agreement = Agreement::factory()->create();
        $agreementId = $agreement->id;

        $result = $this->repository->deleteAgreement($agreement);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('agreements', [
            'id' => $agreementId,
        ]);
    }

    /**
     * Test delete agreement returns true on success.
     */
    public function test_delete_agreement_returns_true_on_success(): void
    {
        $agreement = Agreement::factory()->create();

        $result = $this->repository->deleteAgreement($agreement);

        $this->assertTrue($result);
    }
}
