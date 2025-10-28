<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\ConsentStatus;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\LegalBasis;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserConsentControllerTest extends TestCase
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
            'subject_key' => 'SUBJ-' . uniqid(),
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'consent_purpose' => [ConsentPurpose::MARKETING->value, ConsentPurpose::ANALYTICS->value],
            'consent_status' => ConsentStatus::GRANTED->value,
            'legal_basis' => LegalBasis::CONSENT->value,
            'source_system' => 'Web Portal',
            'evidence_ref' => 'EVD-' . uniqid(),
            'effective_from' => now()->format('Y-m-d'),
            'effective_to' => now()->addYear()->format('Y-m-d'),
            'scope' => 'Email marketing and website analytics',
        ], $overrides);
    }

    /**
     * Test user can get paginated user consents.
     */
    public function test_user_can_get_paginated_user_consents(): void
    {
        UserConsent::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/user-consents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'subject_key',
                            'subject_realm',
                            'jurisdiction',
                            'consent_purpose',
                            'consent_status',
                            'legal_basis',
                            'source_system',
                            'evidence_ref',
                            'effective_from',
                            'effective_to',
                            'scope',
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
     * Test user can get paginated user consents with custom per_page.
     */
    public function test_user_can_get_paginated_user_consents_with_custom_per_page(): void
    {
        UserConsent::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/user-consents?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    /**
     * Test user can create a user consent with all fields.
     */
    public function test_user_can_create_user_consent_with_all_fields(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'subject_key',
                    'subject_realm',
                    'jurisdiction',
                    'consent_purpose',
                    'consent_status',
                    'legal_basis',
                ]
            ])
            ->assertJson([
                'error' => false,
                'message' => 'User consent created successfully',
                'data' => [
                    'subject_key' => $payload['subject_key'],
                    'subject_realm' => $payload['subject_realm'],
                    'jurisdiction' => $payload['jurisdiction'],
                    'consent_status' => $payload['consent_status'],
                    'legal_basis' => $payload['legal_basis'],
                ]
            ]);

        $this->assertDatabaseHas('user_consents', [
            'subject_key' => $payload['subject_key'],
            'consent_status' => $payload['consent_status'],
        ]);
    }

    /**
     * Test user can create a user consent with minimal required fields.
     */
    public function test_user_can_create_user_consent_with_minimal_required_fields(): void
    {
        $payload = $this->validPayload([
            'effective_to' => null,
            'scope' => null,
        ]);

        unset($payload['effective_to'], $payload['scope']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'User consent created successfully',
            ]);
    }

    /**
     * Test user can create consent with multiple purposes.
     */
    public function test_user_can_create_consent_with_multiple_purposes(): void
    {
        $purposes = [
            ConsentPurpose::MARKETING->value,
            ConsentPurpose::ANALYTICS->value,
            ConsentPurpose::PERSONALIZATION->value,
            ConsentPurpose::TRAINING_AI->value,
        ];

        $payload = $this->validPayload(['consent_purpose' => $purposes]);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.consent_purpose', $purposes);
    }

    /**
     * Test create user consent validates subject_key is required.
     */
    public function test_create_user_consent_validates_subject_key_required(): void
    {
        $payload = $this->validPayload(['subject_key' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subject_key');
    }

    /**
     * Test create user consent validates subject_realm is required.
     */
    public function test_create_user_consent_validates_subject_realm_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['subject_realm']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subject_realm');
    }

    /**
     * Test create user consent validates subject_realm is valid enum.
     */
    public function test_create_user_consent_validates_subject_realm_enum(): void
    {
        $payload = $this->validPayload(['subject_realm' => 'invalid_realm']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subject_realm');
    }

    /**
     * Test create user consent validates jurisdiction is required.
     */
    public function test_create_user_consent_validates_jurisdiction_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['jurisdiction']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jurisdiction');
    }

    /**
     * Test create user consent validates jurisdiction is valid enum.
     */
    public function test_create_user_consent_validates_jurisdiction_enum(): void
    {
        $payload = $this->validPayload(['jurisdiction' => 'INVALID']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jurisdiction');
    }

    /**
     * Test create user consent validates consent_purpose is required.
     */
    public function test_create_user_consent_validates_consent_purpose_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['consent_purpose']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('consent_purpose');
    }

    /**
     * Test create user consent validates consent_purpose is array.
     */
    public function test_create_user_consent_validates_consent_purpose_is_array(): void
    {
        $payload = $this->validPayload(['consent_purpose' => 'not_an_array']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('consent_purpose');
    }

    /**
     * Test create user consent validates consent_purpose has at least one item.
     */
    public function test_create_user_consent_validates_consent_purpose_min_one(): void
    {
        $payload = $this->validPayload(['consent_purpose' => []]);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('consent_purpose');
    }

    /**
     * Test create user consent validates consent_purpose values are valid enums.
     */
    public function test_create_user_consent_validates_consent_purpose_values_enum(): void
    {
        $payload = $this->validPayload(['consent_purpose' => ['invalid_purpose']]);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('consent_purpose.0');
    }

    /**
     * Test create user consent validates consent_status is required.
     */
    public function test_create_user_consent_validates_consent_status_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['consent_status']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('consent_status');
    }

    /**
     * Test create user consent validates consent_status is valid enum.
     */
    public function test_create_user_consent_validates_consent_status_enum(): void
    {
        $payload = $this->validPayload(['consent_status' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('consent_status');
    }

    /**
     * Test create user consent validates legal_basis is required.
     */
    public function test_create_user_consent_validates_legal_basis_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['legal_basis']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('legal_basis');
    }

    /**
     * Test create user consent validates legal_basis is valid enum.
     */
    public function test_create_user_consent_validates_legal_basis_enum(): void
    {
        $payload = $this->validPayload(['legal_basis' => 'invalid_basis']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('legal_basis');
    }

    /**
     * Test create user consent validates source_system is required.
     */
    public function test_create_user_consent_validates_source_system_required(): void
    {
        $payload = $this->validPayload(['source_system' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('source_system');
    }

    /**
     * Test create user consent validates evidence_ref is required.
     */
    public function test_create_user_consent_validates_evidence_ref_required(): void
    {
        $payload = $this->validPayload(['evidence_ref' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('evidence_ref');
    }

    /**
     * Test create user consent validates effective_from is required.
     */
    public function test_create_user_consent_validates_effective_from_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['effective_from']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_from');
    }

    /**
     * Test create user consent validates effective_from is valid date.
     */
    public function test_create_user_consent_validates_effective_from_is_date(): void
    {
        $payload = $this->validPayload(['effective_from' => 'not-a-date']);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_from');
    }

    /**
     * Test create user consent validates effective_to is after or equal to effective_from.
     */
    public function test_create_user_consent_validates_effective_to_after_effective_from(): void
    {
        $payload = $this->validPayload([
            'effective_from' => now()->format('Y-m-d'),
            'effective_to' => now()->subDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('effective_to');
    }

    /**
     * Test user can show a specific user consent.
     */
    public function test_user_can_show_specific_user_consent(): void
    {
        $consent = UserConsent::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/user-consents/{$consent->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'User consent retrieved successfully',
                'data' => [
                    'id' => $consent->id,
                    'subject_key' => $consent->subject_key,
                ]
            ]);
    }

    /**
     * Test user can update a user consent.
     */
    public function test_user_can_update_user_consent(): void
    {
        $consent = UserConsent::factory()->create([
            'consent_status' => ConsentStatus::GRANTED->value,
        ]);

        $updatePayload = [
            'consent_status' => ConsentStatus::WITHDRAWN->value,
            'scope' => 'Updated scope for testing',
        ];

        $response = $this->actingAs($this->user)->putJson("/api/user-consents/{$consent->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'User consent updated successfully',
                'data' => [
                    'id' => $consent->id,
                    'consent_status' => ConsentStatus::WITHDRAWN->value,
                    'scope' => 'Updated scope for testing',
                ]
            ]);

        $this->assertDatabaseHas('user_consents', [
            'id' => $consent->id,
            'consent_status' => ConsentStatus::WITHDRAWN->value,
        ]);
    }

    /**
     * Test user can partially update user consent.
     */
    public function test_user_can_partially_update_user_consent(): void
    {
        $consent = UserConsent::factory()->create([
            'subject_key' => 'ORIGINAL-KEY',
            'consent_status' => ConsentStatus::GRANTED->value,
        ]);

        $updatePayload = [
            'subject_key' => 'UPDATED-KEY',
        ];

        $response = $this->actingAs($this->user)->putJson("/api/user-consents/{$consent->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('data.subject_key', 'UPDATED-KEY')
            ->assertJsonPath('data.consent_status', ConsentStatus::GRANTED->value);
    }

    /**
     * Test user can delete a user consent.
     */
    public function test_user_can_delete_user_consent(): void
    {
        $consent = UserConsent::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/user-consents/{$consent->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'User consent deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('user_consents', ['id' => $consent->id]);
    }

    /**
     * Test unauthenticated user cannot access index.
     */
    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/user-consents');

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot create consent.
     */
    public function test_unauthenticated_user_cannot_create_consent(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/user-consents', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot update consent.
     */
    public function test_unauthenticated_user_cannot_update_consent(): void
    {
        $consent = UserConsent::factory()->create();

        $response = $this->putJson("/api/user-consents/{$consent->id}", [
            'scope' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot delete consent.
     */
    public function test_unauthenticated_user_cannot_delete_consent(): void
    {
        $consent = UserConsent::factory()->create();

        $response = $this->deleteJson("/api/user-consents/{$consent->id}");

        $response->assertStatus(401);
    }

    /**
     * Test show returns 404 for non-existent consent.
     */
    public function test_show_returns_404_for_non_existent_consent(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/user-consents/999999');

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
            $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);
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
        ];

        foreach ($jurisdictions as $jurisdiction) {
            $payload = $this->validPayload(['jurisdiction' => $jurisdiction->value]);
            $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);
            $response->assertStatus(201);
        }
    }

    /**
     * Test create handles all consent statuses.
     */
    public function test_create_handles_all_consent_statuses(): void
    {
        $statuses = [
            ConsentStatus::GRANTED,
            ConsentStatus::DENIED,
            ConsentStatus::WITHDRAWN,
            ConsentStatus::EXPIRED,
            ConsentStatus::NOT_OBTAINED,
        ];

        foreach ($statuses as $status) {
            $payload = $this->validPayload(['consent_status' => $status->value]);
            $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);
            $response->assertStatus(201);
        }
    }

    /**
     * Test create handles all legal bases.
     */
    public function test_create_handles_all_legal_bases(): void
    {
        $legalBases = [
            LegalBasis::CONSENT,
            LegalBasis::CONTRACT,
            LegalBasis::LEGAL_OBLIGATION,
            LegalBasis::LEGITIMATE_INTERESTS,
            LegalBasis::PUBLIC_TASK,
            LegalBasis::VITAL_INTERESTS,
        ];

        foreach ($legalBases as $legalBasis) {
            $payload = $this->validPayload(['legal_basis' => $legalBasis->value]);
            $response = $this->actingAs($this->user)->postJson('/api/user-consents', $payload);
            $response->assertStatus(201);
        }
    }
}
