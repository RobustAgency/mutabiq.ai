<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Stakeholder;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VendorControllerTest extends TestCase
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
        $stakeholder = Stakeholder::factory()->create();

        return array_merge([
            'vendor_name' => 'Test Vendor',
            'legal_name' => 'Test Vendor Inc.',
            'hq_country' => 'US',
            'risk_tier' => 'tier_1',
            'status' => 'approved',
            'stakeholder_id' => $stakeholder->id,
            'primary_contacts' => [
                [
                    'name' => 'John Doe',
                    'email' => 'john@testvendor.com',
                    'role' => 'Account Manager',
                    'phone' => '+1-555-0100',
                    'primary' => true,
                ],
            ],
            'metadata' => [
                'website' => 'https://testvendor.com',
                'sub_processors_url' => 'https://testvendor.com/sub-processors',
                'residency_options' => ['US', 'EU'],
            ],
            'notes' => 'Test vendor notes',
        ], $overrides);
    }

    /**
     * Test user can get paginated vendors.
     */
    public function test_user_can_get_paginated_vendors(): void
    {
        Vendor::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/vendors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'vendor_name',
                            'legal_name',
                            'hq_country',
                            'risk_tier',
                            'status',
                            'stakeholder_id',
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
     * Test user can get paginated vendors with custom per_page.
     */
    public function test_user_can_get_paginated_vendors_with_custom_per_page(): void
    {
        Vendor::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/vendors?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    /**
     * Test guest cannot access vendors index.
     */
    public function test_guest_cannot_access_vendors_index(): void
    {
        $response = $this->getJson('/api/vendors');

        $response->assertStatus(401);
    }

    /**
     * Test user can create a vendor with all fields.
     */
    public function test_user_can_create_vendor_with_all_fields(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'vendor_name',
                    'legal_name',
                    'hq_country',
                    'risk_tier',
                    'status',
                    'stakeholder_id',
                    'primary_contacts',
                    'metadata',
                    'notes',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Vendor created successfully',
                'data' => [
                    'vendor_name' => $payload['vendor_name'],
                    'legal_name' => $payload['legal_name'],
                    'hq_country' => $payload['hq_country'],
                ],
            ]);

        $this->assertDatabaseHas('vendors', [
            'vendor_name' => $payload['vendor_name'],
            'legal_name' => $payload['legal_name'],
        ]);
    }

    /**
     * Test user can create a vendor with only required fields.
     */
    public function test_user_can_create_vendor_with_only_required_fields(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $payload = [
            'vendor_name' => 'Minimal Vendor',
            'legal_name' => 'Minimal Vendor LLC',
            'hq_country' => 'GB',
            'risk_tier' => 'tier_2',
            'status' => 'evaluating',
            'stakeholder_id' => $stakeholder->id,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'data' => [
                    'vendor_name' => $payload['vendor_name'],
                ],
            ]);

        $this->assertDatabaseHas('vendors', [
            'vendor_name' => $payload['vendor_name'],
        ]);
    }

    /**
     * Test create vendor requires vendor_name.
     */
    public function test_create_vendor_requires_vendor_name(): void
    {
        $payload = $this->validPayload(['vendor_name' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('vendor_name');
    }

    /**
     * Test create vendor requires legal_name.
     */
    public function test_create_vendor_requires_legal_name(): void
    {
        $payload = $this->validPayload(['legal_name' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('legal_name');
    }

    /**
     * Test create vendor requires hq_country.
     */
    public function test_create_vendor_requires_hq_country(): void
    {
        $payload = $this->validPayload(['hq_country' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('hq_country');
    }

    /**
     * Test create vendor validates hq_country is exactly 2 characters.
     */
    public function test_create_vendor_validates_hq_country_is_exactly_2_characters(): void
    {
        $payload = $this->validPayload(['hq_country' => 'USA']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('hq_country');
    }

    /**
     * Test create vendor validates hq_country is uppercase.
     */
    public function test_create_vendor_validates_hq_country_is_uppercase(): void
    {
        $payload = $this->validPayload(['hq_country' => 'us']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('hq_country');
    }

    /**
     * Test create vendor requires risk_tier.
     */
    public function test_create_vendor_requires_risk_tier(): void
    {
        $payload = $this->validPayload(['risk_tier' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('risk_tier');
    }

    /**
     * Test create vendor validates risk_tier enum.
     */
    public function test_create_vendor_validates_risk_tier_enum(): void
    {
        $payload = $this->validPayload(['risk_tier' => 'invalid_tier']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('risk_tier');
    }

    /**
     * Test create vendor accepts valid risk_tier values.
     */
    public function test_create_vendor_accepts_valid_risk_tier_values(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $validTiers = ['tier_1', 'tier_2', 'tier_3', 'tier_4'];

        foreach ($validTiers as $tier) {
            $payload = [
                'vendor_name' => "Vendor {$tier}",
                'legal_name' => "Vendor {$tier} Inc.",
                'hq_country' => 'US',
                'risk_tier' => $tier,
                'status' => 'approved',
                'stakeholder_id' => $stakeholder->id,
            ];

            $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test create vendor requires status.
     */
    public function test_create_vendor_requires_status(): void
    {
        $payload = $this->validPayload(['status' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * Test create vendor validates status enum.
     */
    public function test_create_vendor_validates_status_enum(): void
    {
        $payload = $this->validPayload(['status' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * Test create vendor accepts valid status values.
     */
    public function test_create_vendor_accepts_valid_status_values(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $validStatuses = ['evaluating', 'approved', 'conditionally_approved', 'restricted', 'suspended', 'terminated'];

        foreach ($validStatuses as $index => $status) {
            $payload = [
                'vendor_name' => "Vendor Status {$index}",
                'legal_name' => "Vendor Status {$index} Inc.",
                'hq_country' => 'US',
                'risk_tier' => 'tier_1',
                'status' => $status,
                'stakeholder_id' => $stakeholder->id,
            ];

            $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

            $response->assertStatus(201);
        }
    }

    /**
     * Test create vendor requires stakeholder_id.
     */
    public function test_create_vendor_requires_stakeholder_id(): void
    {
        $payload = $this->validPayload();
        unset($payload['stakeholder_id']);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('stakeholder_id');
    }

    /**
     * Test create vendor validates stakeholder_id exists.
     */
    public function test_create_vendor_validates_stakeholder_id_exists(): void
    {
        $payload = $this->validPayload(['stakeholder_id' => 99999]);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('stakeholder_id');
    }

    /**
     * Test create vendor validates primary_contacts array structure.
     */
    public function test_create_vendor_validates_primary_contacts_array_structure(): void
    {
        $payload = $this->validPayload([
            'primary_contacts' => [
                ['name' => '', 'email' => 'test@test.com'], // missing required name
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('primary_contacts.0.name');
    }

    /**
     * Test create vendor validates primary_contacts email format.
     */
    public function test_create_vendor_validates_primary_contacts_email_format(): void
    {
        $payload = $this->validPayload([
            'primary_contacts' => [
                ['name' => 'Test', 'email' => 'invalid-email'],
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('primary_contacts.0.email');
    }

    /**
     * Test create vendor accepts multiple contacts.
     */
    public function test_create_vendor_accepts_multiple_contacts(): void
    {
        $payload = $this->validPayload([
            'primary_contacts' => [
                ['name' => 'Contact 1', 'email' => 'contact1@test.com', 'primary' => true],
                ['name' => 'Contact 2', 'email' => 'contact2@test.com', 'primary' => false],
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/vendors', $payload);

        $response->assertStatus(201);
    }

    /**
     * Test guest cannot create vendor.
     */
    public function test_guest_cannot_create_vendor(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/vendors', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test user can view a specific vendor.
     */
    public function test_user_can_view_specific_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/vendors/{$vendor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Vendor retrieved successfully',
                'data' => [
                    'id' => $vendor->id,
                    'vendor_name' => $vendor->vendor_name,
                    'legal_name' => $vendor->legal_name,
                ],
            ]);
    }

    /**
     * Test user cannot view non-existent vendor.
     */
    public function test_user_cannot_view_non_existent_vendor(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/vendors/99999');

        $response->assertStatus(404);
    }

    /**
     * Test guest cannot view vendor.
     */
    public function test_guest_cannot_view_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->getJson("/api/vendors/{$vendor->id}");

        $response->assertStatus(401);
    }

    /**
     * Test user can update a vendor.
     */
    public function test_user_can_update_vendor(): void
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
        ];

        $response = $this->actingAs($this->user)->postJson("/api/vendors/{$vendor->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Vendor updated successfully',
                'data' => [
                    'id' => $vendor->id,
                    'vendor_name' => $updateData['vendor_name'],
                    'legal_name' => $updateData['legal_name'],
                ],
            ]);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'vendor_name' => $updateData['vendor_name'],
        ]);
    }

    /**
     * Test user can partially update a vendor.
     */
    public function test_user_can_partially_update_vendor(): void
    {
        $vendor = Vendor::factory()->create([
            'vendor_name' => 'Original Name',
            'legal_name' => 'Original Legal Name',
            'status' => 'evaluating',
        ]);

        $updateData = ['status' => 'approved'];

        $response = $this->actingAs($this->user)->postJson("/api/vendors/{$vendor->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'data' => [
                    'vendor_name' => 'Original Name',
                    'legal_name' => 'Original Legal Name',
                    'status' => 'approved',
                ],
            ]);
    }

    /**
     * Test update vendor validates hq_country format.
     */
    public function test_update_vendor_validates_hq_country_format(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/vendors/{$vendor->id}", [
            'hq_country' => 'USA',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('hq_country');
    }

    /**
     * Test update vendor validates risk_tier enum.
     */
    public function test_update_vendor_validates_risk_tier_enum(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/vendors/{$vendor->id}", [
            'risk_tier' => 'invalid_tier',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('risk_tier');
    }

    /**
     * Test update vendor validates status enum.
     */
    public function test_update_vendor_validates_status_enum(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/vendors/{$vendor->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * Test update vendor validates stakeholder_id exists.
     */
    public function test_update_vendor_validates_stakeholder_id_exists(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/vendors/{$vendor->id}", [
            'stakeholder_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('stakeholder_id');
    }

    /**
     * Test guest cannot update vendor.
     */
    public function test_guest_cannot_update_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->postJson("/api/vendors/{$vendor->id}", [
            'vendor_name' => 'Updated Name',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test user can delete a vendor.
     */
    public function test_user_can_delete_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/vendors/{$vendor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Vendor deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('vendors', [
            'id' => $vendor->id,
        ]);
    }

    /**
     * Test user cannot delete non-existent vendor.
     */
    public function test_user_cannot_delete_non_existent_vendor(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/vendors/99999');

        $response->assertStatus(404);
    }

    /**
     * Test guest cannot delete vendor.
     */
    public function test_guest_cannot_delete_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->deleteJson("/api/vendors/{$vendor->id}");

        $response->assertStatus(401);
    }

    /**
     * Test vendor json fields are properly returned.
     */
    public function test_vendor_json_fields_are_properly_returned(): void
    {
        $contacts = [
            ['name' => 'Primary', 'email' => 'primary@test.com', 'primary' => true],
            ['name' => 'Secondary', 'email' => 'secondary@test.com', 'primary' => false],
        ];
        $metadata = [
            'website' => 'https://vendor.com',
            'sub_processors_url' => 'https://vendor.com/sub-processors',
        ];

        $vendor = Vendor::factory()->create([
            'primary_contacts' => $contacts,
            'metadata' => $metadata,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/vendors/{$vendor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'primary_contacts' => $contacts,
                    'metadata' => $metadata,
                ],
            ]);
    }

    /**
     * Test vendor with null json fields returns null.
     */
    public function test_vendor_with_null_json_fields_returns_null(): void
    {
        $vendor = Vendor::factory()->create([
            'primary_contacts' => null,
            'metadata' => null,
            'notes' => null,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/vendors/{$vendor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'primary_contacts' => null,
                    'metadata' => null,
                    'notes' => null,
                ],
            ]);
    }
}
