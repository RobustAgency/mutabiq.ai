<?php

namespace Tests\Feature\Repositories;

use App\Models\PdpProcessingRegister;
use App\Models\Organization;
use App\Repositories\PdpProcessingRegisterRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdpProcessingRegisterRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PdpProcessingRegisterRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdpProcessingRegisterRepository();
    }

    /**
     * Test get paginated registers returns paginated results.
     */
    public function test_get_paginated_registers_returns_paginated_results(): void
    {
        $organization = Organization::factory()->create();
        PdpProcessingRegister::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getPaginatedRegisters($organization->id, 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /**
     * Test get paginated registers uses default per page value.
     */
    public function test_get_paginated_registers_uses_default_per_page(): void
    {
        $organization = Organization::factory()->create();
        PdpProcessingRegister::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getPaginatedRegisters($organization->id);

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get paginated registers orders by created_at descending.
     */
    public function test_get_paginated_registers_orders_by_created_at_desc(): void
    {
        $organization = Organization::factory()->create();
        $oldRegister = PdpProcessingRegister::factory()->create([
            'created_at' => now()->subDays(10),
            'organization_id' => $organization->id,
        ]);
        $newRegister = PdpProcessingRegister::factory()->create([
            'created_at' => now(),
            'organization_id' => $organization->id,
        ]);

        $result = $this->repository->getPaginatedRegisters($organization->id);

        $this->assertEquals($newRegister->id, $result->items()[0]->id);
        $this->assertEquals($oldRegister->id, $result->items()[1]->id);
    }

    /**
     * Test get paginated registers returns empty when no records.
     */
    public function test_get_paginated_registers_returns_empty_when_no_records(): void
    {
        $organization = Organization::factory()->create();
        $result = $this->repository->getPaginatedRegisters($organization->id);

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test create register creates new record with all fields.
     */
    public function test_create_register_creates_new_record_with_all_fields(): void
    {
        $organization = Organization::factory()->create();
        $data = [
            'organization_id' => $organization->id,
            'processing_id' => 'PDP-TEST001',
            'purpose' => 'AI model training',
            'controller_role' => 'Controller',
            'data_subject_categories' => 'Anything',
            'personal_data_categories' => ['Identifier', 'Contact', 'Demographic'],
            'lawful_basis' => 'Data Protection',
            'lawful_basis_detail' => 'Explicit consent obtained',
            'retention_policy_ref' => 'RET-2024',
            'recipients' => ['External Processor', 'Cloud Provider'],
            'international_transfer_ref' => 'SCC-2021',
            'dpia_required_flag' => 'Yes',
            'security_measures_ref' => 'SEC-9001',
            'owner_team' => 'Data Science Team',
            'effective_from' => now(),
            'effective_to' => now()->addYear(),
            'status' => 'published',
        ];

        $register = $this->repository->createRegister($data);

        $this->assertInstanceOf(PdpProcessingRegister::class, $register);
        $this->assertEquals($data['purpose'], $register->purpose);
        $this->assertEquals($data['controller_role'], $register->controller_role);
        $this->assertEquals($data['data_subject_categories'], $register->data_subject_categories);
        $this->assertEquals($data['personal_data_categories'], $register->personal_data_categories);
        $this->assertNotNull($register->id);
    }

    /**
     * Test create register with minimal required fields.
     */
    public function test_create_register_with_minimal_fields(): void
    {
        $organization = Organization::factory()->create();

        $data = [
            'organization_id' => $organization->id,
            'processing_id' => 'PDP-MIN001',
            'purpose' => 'Analytics',
            'controller_role' => 'Processor',
            'data_subject_categories' => ['Identifier', 'Contact', 'Demographic'],
            'personal_data_categories' => ['Identifier'],
            'lawful_basis' => 'Data Protection',
            'owner_team' => 'Engineering Team',
            'status' => 'draft',
        ];

        $register = $this->repository->createRegister($data);

        $this->assertInstanceOf(PdpProcessingRegister::class, $register);
        $this->assertNull($register->lawful_basis_detail);
        $this->assertNull($register->retention_policy_ref);
        $this->assertNull($register->recipients);
    }

    /**
     * Test create register with multiple data subject categories.
     */
    public function test_create_register_with_multiple_data_subject_categories(): void
    {
        $organization = Organization::factory()->create();
        $categories = [
            'customer',
            'prospect',
            'employee',
        ];

        $data = [
            'organization_id' => $organization->id,
            'processing_id' => 'PDP-MULTI001',
            'purpose' => 'Fraud detection',
            'controller_role' => 'Controller',
            'data_subject_categories' => $categories,
            'personal_data_categories' => ['Identifier'],
            'lawful_basis' => 'legitimate_interests',
            'owner_team' => 'Compliance Team',
            'status' => 'approved',
        ];

        $register = $this->repository->createRegister($data);

        $this->assertEquals($categories, $register->data_subject_categories);
    }

    /**
     * Test update register updates fields.
     */
    public function test_update_register_updates_fields(): void
    {
        $register = PdpProcessingRegister::factory()->create([
            'purpose' => 'Original purpose',
            'status' => 'draft',
        ]);

        $updateData = [
            'purpose' => 'Updated purpose',
            'status' => 'approved',
        ];

        $updated = $this->repository->updateRegister($register, $updateData);

        $this->assertEquals('Updated purpose', $updated->purpose);
        $this->assertEquals('approved', $updated->status);
    }

    /**
     * Test update register returns fresh instance.
     */
    public function test_update_register_returns_fresh_instance(): void
    {
        $register = PdpProcessingRegister::factory()->create([
            'purpose' => 'Original',
        ]);

        $updateData = [
            'purpose' => 'Updated',
        ];

        $updated = $this->repository->updateRegister($register, $updateData);

        $this->assertNotSame($register, $updated);
        $this->assertEquals('Updated', $updated->purpose);
    }

    /**
     * Test update register partial update.
     */
    public function test_update_register_partial_update(): void
    {
        $register = PdpProcessingRegister::factory()->create([
            'purpose' => 'Original purpose',
            'controller_role' => 'Controller',
            'owner_team' => 'Data Science Team',
        ]);

        $updateData = [
            'purpose' => 'New purpose',
        ];

        $updated = $this->repository->updateRegister($register, $updateData);

        $this->assertEquals('New purpose', $updated->purpose);
        $this->assertEquals('Controller', $updated->controller_role);
        $this->assertEquals('Data Science Team', $updated->owner_team);
    }

    /**
     * Test delete register removes record.
     */
    public function test_delete_register_removes_record(): void
    {
        $register = PdpProcessingRegister::factory()->create();

        $result = $this->repository->deleteRegister($register);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('pdp_processing_registers', [
            'id' => $register->id,
        ]);
    }
}
