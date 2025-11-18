<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Agreement;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgreementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        $vendor = Vendor::factory()->create();

        return array_merge([
            'vendor_id' => $vendor->id,
            'agreement_type' => 'dpa',
            'status' => 'active',
            'effective_from' => now()->format('Y-m-d H:i:s'),
            'effective_to' => now()->addYear()->format('Y-m-d H:i:s'),
            'training_opt_out' => 'yes',
            'audit_rights' => 'yes',
            'transfer_mechanism' => 'sccs',
            'sla_terms' => [
                'availability_target_pct' => 99.9,
                'latency_p95_ms' => 200,
                'support_tier' => 'premium',
                'breach_definition' => 'Service unavailable for more than 1 hour',
                'credit_schedule_ref' => 'SLA-CREDIT-2024',
                'monitoring_ref' => 'MON-2024',
            ],
            'doc_ref' => 'https://example.com/agreement.pdf',
        ], $overrides);
    }

    /**
     * Test user can get paginated agreements.
     */
    public function test_user_can_get_paginated_agreements(): void
    {
        Agreement::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/agreements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'agreement_type',
                            'status',
                            'effective_from',
                            'effective_to',
                            'doc_ref',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ])
            ->assertJson(['error' => false]);
    }

    /**
     * Test user can get paginated agreements with custom per_page.
     */
    public function test_user_can_get_paginated_agreements_with_custom_per_page(): void
    {
        Agreement::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/agreements?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    /**
     * Test guest cannot access agreements index.
     */
    public function test_guest_cannot_access_agreements_index(): void
    {
        $response = $this->getJson('/api/agreements');

        $response->assertStatus(401);
    }

    /**
     * Test user can create an agreement with all fields.
     */
    public function test_user_can_create_agreement_with_all_fields(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'agreement_type',
                    'status',
                    'effective_from',
                    'effective_to',
                    'training_opt_out',
                    'audit_rights',
                    'transfer_mechanism',
                    'sla_terms',
                    'doc_ref',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Agreement created successfully',
                'data' => [
                    'agreement_type' => $payload['agreement_type'],
                    'status' => $payload['status'],
                ],
            ]);

        $this->assertDatabaseHas('agreements', [
            'vendor_id' => $payload['vendor_id'],
            'agreement_type' => $payload['agreement_type'],
        ]);
    }

    /**
     * Test user can create an agreement with only required fields.
     */
    public function test_user_can_create_agreement_with_only_required_fields(): void
    {
        $vendor = Vendor::factory()->create();
        $payload = [
            'vendor_id' => $vendor->id,
            'agreement_type' => 'msa',
            'status' => 'draft',
            'effective_from' => now()->format('Y-m-d H:i:s'),
            'effective_to' => now()->addYear()->format('Y-m-d H:i:s'),
            'doc_ref' => 'https://example.com/msa.pdf',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'data' => [
                    'agreement_type' => $payload['agreement_type'],
                ],
            ]);

        $this->assertDatabaseHas('agreements', [
            'agreement_type' => $payload['agreement_type'],
        ]);
    }

    /**
     * Test create agreement requires vendor_id.
     */
    public function test_create_agreement_requires_vendor_id(): void
    {
        $payload = $this->validPayload();
        unset($payload['vendor_id']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('vendor_id');
    }

    /**
     * Test create agreement validates vendor_id exists.
     */
    public function test_create_agreement_validates_vendor_id_exists(): void
    {
        $payload = $this->validPayload(['vendor_id' => 99999]);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('vendor_id');
    }

    /**
     * Test create agreement requires agreement_type.
     */
    public function test_create_agreement_requires_agreement_type(): void
    {
        $payload = $this->validPayload(['agreement_type' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('agreement_type');
    }

    /**
     * Test create agreement validates agreement_type enum.
     */
    public function test_create_agreement_validates_agreement_type_enum(): void
    {
        $payload = $this->validPayload(['agreement_type' => 'invalid_type']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('agreement_type');
    }

    /**
     * Test create agreement accepts valid agreement_type values.
     */
    public function test_create_agreement_accepts_valid_agreement_type_values(): void
    {
        $vendor = Vendor::factory()->create();
        $validTypes = ['msa', 'dpa', 'order_form', 'addendum', 'sla', 'other'];

        foreach ($validTypes as $type) {
            $payload = [
                'vendor_id' => $vendor->id,
                'agreement_type' => $type,
                'status' => 'active',
                'effective_from' => now()->format('Y-m-d H:i:s'),
                'effective_to' => now()->addYear()->format('Y-m-d H:i:s'),
                'doc_ref' => "https://example.com/{$type}.pdf",
            ];

            $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test create agreement requires status.
     */
    public function test_create_agreement_requires_status(): void
    {
        $payload = $this->validPayload(['status' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * Test create agreement validates status enum.
     */
    public function test_create_agreement_validates_status_enum(): void
    {
        $payload = $this->validPayload(['status' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * Test create agreement accepts valid status values.
     */
    public function test_create_agreement_accepts_valid_status_values(): void
    {
        $vendor = Vendor::factory()->create();
        $validStatuses = ['draft', 'active', 'lapsed', 'terminated'];

        foreach ($validStatuses as $index => $status) {
            $payload = [
                'vendor_id' => $vendor->id,
                'agreement_type' => 'msa',
                'status' => $status,
                'effective_from' => now()->format('Y-m-d H:i:s'),
                'effective_to' => now()->addYear()->format('Y-m-d H:i:s'),
                'doc_ref' => "https://example.com/status-{$index}.pdf",
            ];

            $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test create agreement requires effective_from.
     */
    public function test_create_agreement_requires_effective_from(): void
    {
        $payload = $this->validPayload();
        unset($payload['effective_from']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_from');
    }

    /**
     * Test create agreement requires effective_to.
     */
    public function test_create_agreement_requires_effective_to(): void
    {
        $payload = $this->validPayload();
        unset($payload['effective_to']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_to');
    }

    /**
     * Test create agreement validates effective_to is after effective_from.
     */
    public function test_create_agreement_validates_effective_to_is_after_effective_from(): void
    {
        $payload = $this->validPayload([
            'effective_from' => now()->addYear()->format('Y-m-d H:i:s'),
            'effective_to' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_to');
    }

    /**
     * Test create agreement validates training_opt_out enum.
     */
    public function test_create_agreement_validates_training_opt_out_enum(): void
    {
        $payload = $this->validPayload(['training_opt_out' => 'invalid']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('training_opt_out');
    }

    /**
     * Test create agreement accepts valid training_opt_out values.
     */
    public function test_create_agreement_accepts_valid_training_opt_out_values(): void
    {
        $vendor = Vendor::factory()->create();
        $validValues = ['yes', 'no', 'not_applicable'];

        foreach ($validValues as $index => $value) {
            $payload = [
                'vendor_id' => $vendor->id,
                'agreement_type' => 'dpa',
                'status' => 'active',
                'effective_from' => now()->format('Y-m-d H:i:s'),
                'effective_to' => now()->addYear()->format('Y-m-d H:i:s'),
                'training_opt_out' => $value,
                'doc_ref' => "https://example.com/training-{$index}.pdf",
            ];

            $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test create agreement validates audit_rights enum.
     */
    public function test_create_agreement_validates_audit_rights_enum(): void
    {
        $payload = $this->validPayload(['audit_rights' => 'invalid']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('audit_rights');
    }

    /**
     * Test create agreement accepts valid audit_rights values.
     */
    public function test_create_agreement_accepts_valid_audit_rights_values(): void
    {
        $vendor = Vendor::factory()->create();
        $validValues = ['yes', 'no', 'limited'];

        foreach ($validValues as $index => $value) {
            $payload = [
                'vendor_id' => $vendor->id,
                'agreement_type' => 'dpa',
                'status' => 'active',
                'effective_from' => now()->format('Y-m-d H:i:s'),
                'effective_to' => now()->addYear()->format('Y-m-d H:i:s'),
                'audit_rights' => $value,
                'doc_ref' => "https://example.com/audit-{$index}.pdf",
            ];

            $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test create agreement validates transfer_mechanism enum.
     */
    public function test_create_agreement_validates_transfer_mechanism_enum(): void
    {
        $payload = $this->validPayload(['transfer_mechanism' => 'invalid']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('transfer_mechanism');
    }

    /**
     * Test create agreement accepts valid transfer_mechanism values.
     */
    public function test_create_agreement_accepts_valid_transfer_mechanism_values(): void
    {
        $vendor = Vendor::factory()->create();
        $validValues = ['adequacy', 'sccs', 'bcrs', 'dpa_addendum', 'derogation', 'none'];

        foreach ($validValues as $index => $value) {
            $payload = [
                'vendor_id' => $vendor->id,
                'agreement_type' => 'dpa',
                'status' => 'active',
                'effective_from' => now()->format('Y-m-d H:i:s'),
                'effective_to' => now()->addYear()->format('Y-m-d H:i:s'),
                'transfer_mechanism' => $value,
                'doc_ref' => "https://example.com/transfer-{$index}.pdf",
            ];

            $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test create agreement validates sla_terms array structure.
     */
    public function test_create_agreement_validates_sla_terms_array_structure(): void
    {
        $payload = $this->validPayload([
            'sla_terms' => 'not-an-array',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('sla_terms');
    }

    /**
     * Test create agreement validates sla_terms availability_target_pct range.
     */
    public function test_create_agreement_validates_sla_terms_availability_target_pct_range(): void
    {
        $payload = $this->validPayload([
            'sla_terms' => [
                'availability_target_pct' => 150, // Invalid: > 100
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('sla_terms.availability_target_pct');
    }

    /**
     * Test create agreement validates sla_terms latency_p95_ms is positive.
     */
    public function test_create_agreement_validates_sla_terms_latency_p95_ms_is_positive(): void
    {
        $payload = $this->validPayload([
            'sla_terms' => [
                'latency_p95_ms' => -100, // Invalid: negative
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('sla_terms.latency_p95_ms');
    }

    /**
     * Test create agreement accepts valid sla_terms.
     */
    public function test_create_agreement_accepts_valid_sla_terms(): void
    {
        $payload = $this->validPayload([
            'sla_terms' => [
                'availability_target_pct' => 99.95,
                'latency_p95_ms' => 150,
                'support_tier' => 'enterprise',
                'breach_definition' => 'Service down > 1 hour',
                'credit_schedule_ref' => 'SLA-2024',
                'monitoring_ref' => 'MON-2024',
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(201);
    }

    /**
     * Test create agreement requires doc_ref.
     */
    public function test_create_agreement_requires_doc_ref(): void
    {
        $payload = $this->validPayload(['doc_ref' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('doc_ref');
    }

    /**
     * Test create agreement validates doc_ref max length.
     */
    public function test_create_agreement_validates_doc_ref_max_length(): void
    {
        $payload = $this->validPayload(['doc_ref' => str_repeat('a', 501)]);

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('doc_ref');
    }

    /**
     * Test guest cannot create agreement.
     */
    public function test_guest_cannot_create_agreement(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/agreements', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test user can view a specific agreement.
     */
    public function test_user_can_view_specific_agreement(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/agreements/{$agreement->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Agreement retrieved successfully',
                'data' => [
                    'id' => $agreement->id,
                    'agreement_type' => $agreement->agreement_type,
                    'status' => $agreement->status,
                ],
            ]);
    }

    /**
     * Test user cannot view non-existent agreement.
     */
    public function test_user_cannot_view_non_existent_agreement(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/agreements/99999');

        $response->assertStatus(404);
    }

    /**
     * Test guest cannot view agreement.
     */
    public function test_guest_cannot_view_agreement(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->getJson("/api/agreements/{$agreement->id}");

        $response->assertStatus(401);
    }

    /**
     * Test user can update an agreement.
     */
    public function test_user_can_update_agreement(): void
    {
        $agreement = Agreement::factory()->create();
        $newVendor = Vendor::factory()->create();

        $updateData = [
            'vendor_id' => $newVendor->id,
            'agreement_type' => 'order_form',
            'status' => 'lapsed',
            'effective_from' => now()->subYear()->format('Y-m-d H:i:s'),
            'effective_to' => now()->format('Y-m-d H:i:s'),
            'doc_ref' => 'https://updated.com/agreement.pdf',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/agreements/{$agreement->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Agreement updated successfully',
                'data' => [
                    'id' => $agreement->id,
                    'agreement_type' => $updateData['agreement_type'],
                    'status' => $updateData['status'],
                ],
            ]);

        $this->assertDatabaseHas('agreements', [
            'id' => $agreement->id,
            'agreement_type' => $updateData['agreement_type'],
        ]);
    }

    /**
     * Test user can partially update an agreement.
     */
    public function test_user_can_partially_update_agreement(): void
    {
        $agreement = Agreement::factory()->create([
            'agreement_type' => 'msa',
            'status' => 'draft',
        ]);

        $updateData = ['status' => 'active'];

        $response = $this->actingAs($this->user)->postJson("/api/agreements/{$agreement->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'data' => [
                    'agreement_type' => 'msa',
                    'status' => 'active',
                ],
            ]);
    }

    /**
     * Test update agreement validates vendor_id exists.
     */
    public function test_update_agreement_validates_vendor_id_exists(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/agreements/{$agreement->id}", [
            'vendor_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('vendor_id');
    }

    /**
     * Test update agreement validates agreement_type enum.
     */
    public function test_update_agreement_validates_agreement_type_enum(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/agreements/{$agreement->id}", [
            'agreement_type' => 'invalid_type',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('agreement_type');
    }

    /**
     * Test update agreement validates status enum.
     */
    public function test_update_agreement_validates_status_enum(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/agreements/{$agreement->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * Test update agreement validates effective_to after effective_from.
     */
    public function test_update_agreement_validates_effective_to_after_effective_from(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/agreements/{$agreement->id}", [
            'effective_from' => now()->addYear()->format('Y-m-d H:i:s'),
            'effective_to' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_to');
    }

    /**
     * Test guest cannot update agreement.
     */
    public function test_guest_cannot_update_agreement(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->postJson("/api/agreements/{$agreement->id}", [
            'status' => 'active',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test user can delete an agreement.
     */
    public function test_user_can_delete_agreement(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/agreements/{$agreement->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Agreement deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('agreements', [
            'id' => $agreement->id,
        ]);
    }

    /**
     * Test user cannot delete non-existent agreement.
     */
    public function test_user_cannot_delete_non_existent_agreement(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/agreements/99999');

        $response->assertStatus(404);
    }

    /**
     * Test guest cannot delete agreement.
     */
    public function test_guest_cannot_delete_agreement(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->deleteJson("/api/agreements/{$agreement->id}");

        $response->assertStatus(401);
    }

    /**
     * Test agreement sla_terms json fields are properly returned.
     */
    public function test_agreement_sla_terms_json_fields_are_properly_returned(): void
    {
        $slaTerms = [
            'availability_target_pct' => 99.95,
            'latency_p95_ms' => 150,
            'support_tier' => 'premium',
            'breach_definition' => 'Service unavailable > 1 hour',
            'credit_schedule_ref' => 'SLA-2024',
            'monitoring_ref' => 'MON-2024',
        ];

        $agreement = Agreement::factory()->create([
            'sla_terms' => $slaTerms,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/agreements/{$agreement->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'sla_terms' => $slaTerms,
                ],
            ]);
    }

    /**
     * Test agreement with null optional fields returns null.
     */
    public function test_agreement_with_null_optional_fields_returns_null(): void
    {
        $agreement = Agreement::factory()->create([
            'training_opt_out' => null,
            'audit_rights' => null,
            'transfer_mechanism' => null,
            'sla_terms' => null,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/agreements/{$agreement->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'training_opt_out' => null,
                    'audit_rights' => null,
                    'transfer_mechanism' => null,
                    'sla_terms' => null,
                ],
            ]);
    }

    /**
     * Test create agreement with SLA type and sla_terms.
     */
    public function test_create_agreement_with_sla_type_and_sla_terms(): void
    {
        $vendor = Vendor::factory()->create();
        $payload = [
            'vendor_id' => $vendor->id,
            'agreement_type' => 'sla',
            'status' => 'active',
            'effective_from' => now()->format('Y-m-d H:i:s'),
            'effective_to' => now()->addYear()->format('Y-m-d H:i:s'),
            'sla_terms' => [
                'availability_target_pct' => 99.9,
                'latency_p95_ms' => 100,
                'support_tier' => 'enterprise',
            ],
            'doc_ref' => 'https://example.com/sla.pdf',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/agreements', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.agreement_type', 'sla')
            ->assertJsonPath('data.sla_terms.availability_target_pct', 99.9);
    }
}
