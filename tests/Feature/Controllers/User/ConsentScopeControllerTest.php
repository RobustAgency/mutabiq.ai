<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\ConsentScope;
use App\Models\Dataset;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentScopeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    private function validPayload(array $overrides = []): array
    {
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);
        $selectedPurposes = fake()->randomElements(ConsentPurpose::cases(), fake()->numberBetween(1, 4));
        $purposeValues = array_map(fn($purpose) => $purpose->value, $selectedPurposes);

        return array_merge([
            'dataset_id' => $dataset->id,
            'purpose' => $purposeValues,
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'effective_from' => now()->format('Y-m-d'),
            'effective_to' => now()->addYear()->format('Y-m-d'),
        ], $overrides);
    }

    /**
     * Test user can get paginated consent scopes.
     */
    public function test_user_can_get_paginated_consent_scopes(): void
    {
        ConsentScope::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/consent-scopes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'dataset_id',
                            'purpose',
                            'subject_realm',
                            'jurisdiction',
                            'effective_from',
                            'effective_to',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'per_page',
                    'total',
                ]
            ])
            ->assertJson(['error' => false]);
    }

    /**
     * Test user can get paginated consent scopes with custom per_page.
     */
    public function test_user_can_get_paginated_consent_scopes_with_custom_per_page(): void
    {
        ConsentScope::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/consent-scopes?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    /**
     * Test user can create a consent scope with all fields.
     */
    public function test_user_can_create_consent_scope_with_all_fields(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'dataset_id',
                    'purpose',
                    'subject_realm',
                    'jurisdiction',
                    'effective_from',
                    'effective_to',
                ]
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Consent scope created successfully',
                'data' => [
                    'dataset_id' => $payload['dataset_id'],
                    'subject_realm' => $payload['subject_realm'],
                    'jurisdiction' => $payload['jurisdiction'],
                ]
            ]);

        $this->assertDatabaseHas('consent_scopes', [
            'dataset_id' => $payload['dataset_id'],
            'purpose' => json_encode($payload['purpose']),
        ]);
    }

    /**
     * Test user can create a consent scope with minimal required fields.
     */
    public function test_user_can_create_consent_scope_with_minimal_required_fields(): void
    {
        $payload = $this->validPayload([
            'effective_to' => null,
        ]);

        unset($payload['effective_to']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Consent scope created successfully',
            ]);
    }

    /**
     * Test create consent scope validates dataset_id is required.
     */
    public function test_create_consent_scope_validates_dataset_id_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['dataset_id']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('dataset_id');
    }

    /**
     * Test create consent scope validates dataset_id exists.
     */
    public function test_create_consent_scope_validates_dataset_id_exists(): void
    {
        $payload = $this->validPayload(['dataset_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('dataset_id');
    }

    /**
     * Test create consent scope validates purpose is required.
     */
    public function test_create_consent_scope_validates_purpose_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['purpose']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('purpose');
    }

    /**
     * Test create consent scope validates purpose is valid enum.
     */
    public function test_create_consent_scope_validates_purpose_enum(): void
    {
        $payload = $this->validPayload(['purpose' => 'invalid_purpose']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('purpose');
    }

    /**
     * Test create consent scope validates subject_realm is required.
     */
    public function test_create_consent_scope_validates_subject_realm_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['subject_realm']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subject_realm');
    }

    /**
     * Test create consent scope validates subject_realm is valid enum.
     */
    public function test_create_consent_scope_validates_subject_realm_enum(): void
    {
        $payload = $this->validPayload(['subject_realm' => 'invalid_realm']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subject_realm');
    }

    /**
     * Test create consent scope validates jurisdiction is required.
     */
    public function test_create_consent_scope_validates_jurisdiction_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['jurisdiction']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jurisdiction');
    }

    /**
     * Test create consent scope validates jurisdiction is valid enum.
     */
    public function test_create_consent_scope_validates_jurisdiction_enum(): void
    {
        $payload = $this->validPayload(['jurisdiction' => 'INVALID']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jurisdiction');
    }

    /**
     * Test create consent scope validates effective_from is required.
     */
    public function test_create_consent_scope_validates_effective_from_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['effective_from']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_from');
    }

    /**
     * Test create consent scope validates effective_from is valid date.
     */
    public function test_create_consent_scope_validates_effective_from_is_date(): void
    {
        $payload = $this->validPayload(['effective_from' => 'not-a-date']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_from');
    }

    /**
     * Test create consent scope validates effective_to is after or equal to effective_from.
     */
    public function test_create_consent_scope_validates_effective_to_after_effective_from(): void
    {
        $payload = $this->validPayload([
            'effective_from' => now()->format('Y-m-d'),
            'effective_to' => now()->subDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_to');
    }

    /**
     * Test user can show a specific consent scope.
     */
    public function test_user_can_show_specific_consent_scope(): void
    {
        $consentScope = ConsentScope::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson("/api/consent-scopes/{$consentScope->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Consent scope retrieved successfully',
                'data' => [
                    'id' => $consentScope->id,
                    'dataset_id' => $consentScope->dataset_id,
                ]
            ]);
    }

    /**
     * Test user can update a consent scope.
     */
    public function test_user_can_update_consent_scope(): void
    {
        $consentScope = ConsentScope::factory()->create([
            'organization_id' => $this->organization->id,
            'purpose' => [ConsentPurpose::MARKETING->value],
        ]);

        $updatePayload = [
            'purpose' => [ConsentPurpose::ANALYTICS->value],
            'jurisdiction' => Jurisdiction::US->value,
        ];

        $response = $this->actingAs($this->user)->putJson("/api/consent-scopes/{$consentScope->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Consent scope updated successfully',
                'data' => [
                    'id' => $consentScope->id,
                    'purpose' => $updatePayload['purpose'],
                    'jurisdiction' => Jurisdiction::US->value,
                ]
            ]);

        $this->assertDatabaseHas('consent_scopes', [
            'id' => $consentScope->id,
            'purpose' => json_encode([ConsentPurpose::ANALYTICS->value]),
        ]);
    }

    /**
     * Test user can partially update consent scope.
     */
    public function test_user_can_partially_update_consent_scope(): void
    {
        $consentScope = ConsentScope::factory()->create([
            'organization_id' => $this->organization->id,
            'purpose' => [ConsentPurpose::MARKETING->value],
            'jurisdiction' => Jurisdiction::EU->value,
        ]);

        $updatePayload = [
            'purpose' => [ConsentPurpose::TRAINING_AI->value],
        ];

        $response = $this->actingAs($this->user)->putJson("/api/consent-scopes/{$consentScope->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('data.purpose', $updatePayload['purpose'])
            ->assertJsonPath('data.jurisdiction', Jurisdiction::EU->value);
    }

    /**
     * Test user can delete a consent scope.
     */
    public function test_user_can_delete_consent_scope(): void
    {
        $consentScope = ConsentScope::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/consent-scopes/{$consentScope->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Consent scope deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('consent_scopes', ['id' => $consentScope->id]);
    }

    /**
     * Test unauthenticated user cannot access index.
     */
    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/consent-scopes');

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot create consent scope.
     */
    public function test_unauthenticated_user_cannot_create_consent_scope(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot update consent scope.
     */
    public function test_unauthenticated_user_cannot_update_consent_scope(): void
    {
        $consentScope = ConsentScope::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->putJson("/api/consent-scopes/{$consentScope->id}", [
            'purpose' => ConsentPurpose::ANALYTICS->value,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot delete consent scope.
     */
    public function test_unauthenticated_user_cannot_delete_consent_scope(): void
    {
        $consentScope = ConsentScope::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->deleteJson("/api/consent-scopes/{$consentScope->id}");

        $response->assertStatus(401);
    }

    /**
     * Test show returns 404 for non-existent consent scope.
     */
    public function test_show_returns_404_for_non_existent_consent_scope(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/consent-scopes/999999');

        $response->assertStatus(404);
    }

    /**
     * Test create handles all subject realms.
     */
    public function test_create_handles_all_subject_realms(): void
    {
        $realms = [
            SubjectRealm::CUSTOMER,
            SubjectRealm::PROSPECT,
            SubjectRealm::EMPLOYEE,
            SubjectRealm::VENDOR,
            SubjectRealm::OTHER,
        ];

        foreach ($realms as $realm) {
            $payload = $this->validPayload(['subject_realm' => $realm->value]);
            $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);
            $response->assertStatus(201);
        }
    }

    /**
     * Test create handles all jurisdictions.
     */
    public function test_create_handles_all_jurisdictions(): void
    {
        $jurisdictions = [
            Jurisdiction::AE,
            Jurisdiction::EU,
            Jurisdiction::KSA,
            Jurisdiction::US,
            Jurisdiction::UK,
            Jurisdiction::QA,
            Jurisdiction::JO,
        ];

        foreach ($jurisdictions as $jurisdiction) {
            $payload = $this->validPayload(['jurisdiction' => $jurisdiction->value]);
            $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);
            $response->assertStatus(201);
        }
    }

    /**
     * Test consent scope effective_to can be null for indefinite duration.
     */
    public function test_consent_scope_effective_to_can_be_null(): void
    {
        $payload = $this->validPayload();
        unset($payload['effective_to']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-scopes', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.effective_to', null);
    }
}
