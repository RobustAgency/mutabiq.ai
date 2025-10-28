<?php

namespace Tests\Feature\Controllers\User;

use App\Models\PdpProcessingRegister;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdpProcessingRegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'purpose' => 'AI model training',
            'controller_role' => 'Controller',
            'data_subject_categories' => ['customer', 'prospect'],
            'personal_data_categories' => ['Identifier', 'Contact', 'Demographic'],
            'lawful_basis' => 'consent',
            'lawful_basis_detail' => 'Explicit consent obtained',
            'retention_policy_ref' => 'RET-2024',
            'recipients' => ['External Processor', 'Cloud Provider'],
            'international_transfer_ref' => 'SCC-2021',
            'dpia_required_flag' => 'Yes',
            'security_measures_ref' => 'SEC-9001',
            'owner_team' => 'Data Science Team',
            'effective_from' => now()->format('Y-m-d H:i:s'),
            'effective_to' => now()->addYear()->format('Y-m-d H:i:s'),
            'status' => 'draft',
        ], $overrides);
    }

    /**
     * Test user can get paginated registers.
     */
    public function test_user_can_get_paginated_registers(): void
    {
        PdpProcessingRegister::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/pdp-processing-registers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'purpose',
                            'controller_role',
                            'data_subject_categories',
                            'personal_data_categories',
                            'lawful_basis',
                            'owner_team',
                            'status',
                        ]
                    ],
                    'per_page',
                    'total',
                ]
            ])
            ->assertJson(['error' => false]);
    }

    /**
     * Test user can get paginated registers with custom per_page.
     */
    public function test_user_can_get_paginated_registers_with_custom_per_page(): void
    {
        PdpProcessingRegister::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/pdp-processing-registers?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    /**
     * Test user can create a register with all fields.
     */
    public function test_user_can_create_register_with_all_fields(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'purpose',
                    'controller_role',
                ]
            ])
            ->assertJson([
                'error' => false,
                'message' => 'PDP processing register created successfully',
                'data' => [
                    'purpose' => $payload['purpose'],
                    'controller_role' => $payload['controller_role'],
                ]
            ]);

        $this->assertDatabaseHas('pdp_processing_registers', [
            'purpose' => $payload['purpose'],
        ]);
    }

    /**
     * Test user can create register with minimal fields.
     */
    public function test_user_can_create_register_with_minimal_fields(): void
    {
        $payload = [
            'purpose' => 'Fraud detection',
            'controller_role' => 'Processor',
            'data_subject_categories' => ['employee'],
            'personal_data_categories' => ['Identifier'],
            'lawful_basis' => 'contract',
            'owner_team' => 'Engineering Team',
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'PDP processing register created successfully',
            ]);
    }

    /**
     * Test create validates purpose is required.
     */
    public function test_create_validates_purpose_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['purpose']);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('purpose');
    }

    /**
     * Test create validates controller_role is required.
     */
    public function test_create_validates_controller_role_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['controller_role']);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('controller_role');
    }

    /**
     * Test create validates data_subject_categories is required.
     */
    public function test_create_validates_data_subject_categories_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['data_subject_categories']);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('data_subject_categories');
    }

    /**
     * Test create validates data_subject_categories is array.
     */
    public function test_create_validates_data_subject_categories_is_array(): void
    {
        $payload = $this->validPayload(['data_subject_categories' => 'not-an-array']);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('data_subject_categories');
    }

    /**
     * Test create validates data_subject_categories min 1.
     */
    public function test_create_validates_data_subject_categories_min_one(): void
    {
        $payload = $this->validPayload(['data_subject_categories' => []]);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('data_subject_categories');
    }

    /**
     * Test create validates personal_data_categories is required.
     */
    public function test_create_validates_personal_data_categories_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['personal_data_categories']);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('personal_data_categories');
    }

    /**
     * Test create validates personal_data_categories is array.
     */
    public function test_create_validates_personal_data_categories_is_array(): void
    {
        $payload = $this->validPayload(['personal_data_categories' => 'not-an-array']);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('personal_data_categories');
    }

    /**
     * Test create validates personal_data_categories min 1.
     */
    public function test_create_validates_personal_data_categories_min_one(): void
    {
        $payload = $this->validPayload(['personal_data_categories' => []]);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('personal_data_categories');
    }

    /**
     * Test create validates lawful_basis is required.
     */
    public function test_create_validates_lawful_basis_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['lawful_basis']);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('lawful_basis');
    }

    /**
     * Test create validates owner_team is required.
     */
    public function test_create_validates_owner_team_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['owner_team']);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('owner_team');
    }

    /**
     * Test create validates status is required.
     */
    public function test_create_validates_status_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['status']);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * Test create validates effective_to after effective_from.
     */
    public function test_create_validates_effective_to_after_effective_from(): void
    {
        $payload = $this->validPayload([
            'effective_from' => now()->addYear()->format('Y-m-d H:i:s'),
            'effective_to' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_to');
    }

    /**
     * Test user can show a specific register.
     */
    public function test_user_can_show_specific_register(): void
    {
        $register = PdpProcessingRegister::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/pdp-processing-registers/{$register->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'PDP processing register retrieved successfully',
                'data' => [
                    'id' => $register->id,
                ]
            ]);
    }

    /**
     * Test user can update a register.
     */
    public function test_user_can_update_register(): void
    {
        $register = PdpProcessingRegister::factory()->create([
            'purpose' => 'Original purpose',
            'status' => 'draft',
        ]);

        $updatePayload = [
            'purpose' => 'Updated purpose',
            'status' => 'approved',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/pdp-processing-registers/{$register->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'PDP processing register updated successfully',
                'data' => [
                    'id' => $register->id,
                    'purpose' => 'Updated purpose',
                    'status' => 'approved',
                ]
            ]);

        $this->assertDatabaseHas('pdp_processing_registers', [
            'id' => $register->id,
            'purpose' => 'Updated purpose',
        ]);
    }

    /**
     * Test user can partially update register.
     */
    public function test_user_can_partially_update_register(): void
    {
        $register = PdpProcessingRegister::factory()->create([
            'purpose' => 'Original',
            'controller_role' => 'Controller',
        ]);

        $updatePayload = [
            'purpose' => 'Updated',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/pdp-processing-registers/{$register->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('data.purpose', 'Updated')
            ->assertJsonPath('data.controller_role', 'Controller');
    }

    /**
     * Test user can delete a register.
     */
    public function test_user_can_delete_register(): void
    {
        $register = PdpProcessingRegister::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/pdp-processing-registers/{$register->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'PDP processing register deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('pdp_processing_registers', ['id' => $register->id]);
    }

    /**
     * Test unauthenticated user cannot access index.
     */
    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/pdp-processing-registers');

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot create register.
     */
    public function test_unauthenticated_user_cannot_create_register(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/pdp-processing-registers', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot update register.
     */
    public function test_unauthenticated_user_cannot_update_register(): void
    {
        $register = PdpProcessingRegister::factory()->create();

        $response = $this->postJson("/api/pdp-processing-registers/{$register->id}", [
            'purpose' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot delete register.
     */
    public function test_unauthenticated_user_cannot_delete_register(): void
    {
        $register = PdpProcessingRegister::factory()->create();

        $response = $this->deleteJson("/api/pdp-processing-registers/{$register->id}");

        $response->assertStatus(401);
    }

    /**
     * Test show returns 404 for non-existent register.
     */
    public function test_show_returns_404_for_non_existent_register(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/pdp-processing-registers/999999');

        $response->assertStatus(404);
    }

    /**
     * Test create handles all controller roles.
     */
    public function test_create_handles_all_controller_roles(): void
    {
        $roles = ['Controller', 'Processor', 'Joint Controller'];

        foreach ($roles as $role) {
            $payload = $this->validPayload(['controller_role' => $role]);
            $response = $this->actingAs($this->user)->postJson('/api/pdp-processing-registers', $payload);
            $response->assertStatus(201);
        }
    }
}
