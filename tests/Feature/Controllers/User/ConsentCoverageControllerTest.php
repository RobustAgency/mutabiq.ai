<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Models\ConsentCoverage;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentCoverageControllerTest extends TestCase
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
        $dataset = Dataset::factory()->create();

        return array_merge([
            'dataset_id' => $dataset->id,
            'snapshot_id' => DatasetSnapshot::factory()->for($dataset)->create()->id,
            'purpose' => ConsentPurpose::MARKETING->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'as_of' => now()->format('Y-m-d H:i:s'),
            'subjects_total' => 10000,
            'subjects_with_valid_consent' => 8500,
            'coverage_pct' => 85.00,
            'evidence_ref' => 'EVD-' . uniqid(),
        ], $overrides);
    }

    /**
     * Test user can get paginated consent coverages.
     */
    public function test_user_can_get_paginated_consent_coverages(): void
    {
        ConsentCoverage::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/consent-coverages');

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
                            'snapshot_id',
                            'purpose',
                            'jurisdiction',
                            'as_of',
                            'subjects_total',
                            'subjects_with_valid_consent',
                            'coverage_pct',
                            'evidence_ref',
                            'created_at',
                        ]
                    ],
                    'per_page',
                    'total',
                ]
            ])
            ->assertJson(['error' => false]);
    }

    /**
     * Test user can get paginated consent coverages with custom per_page.
     */
    public function test_user_can_get_paginated_consent_coverages_with_custom_per_page(): void
    {
        ConsentCoverage::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/consent-coverages?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    /**
     * Test user can create a consent coverage with all fields.
     */
    public function test_user_can_create_consent_coverage_with_all_fields(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'dataset_id',
                    'snapshot_id',
                    'purpose',
                    'jurisdiction',
                    'subjects_total',
                    'subjects_with_valid_consent',
                    'coverage_pct',
                ]
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Consent coverage created successfully',
                'data' => [
                    'dataset_id' => $payload['dataset_id'],
                    'purpose' => $payload['purpose'],
                    'jurisdiction' => $payload['jurisdiction'],
                    'subjects_total' => $payload['subjects_total'],
                    'subjects_with_valid_consent' => $payload['subjects_with_valid_consent'],
                    'coverage_pct' => $payload['coverage_pct'],
                ]
            ]);

        $this->assertDatabaseHas('consent_coverages', [
            'dataset_id' => $payload['dataset_id'],
            'subjects_total' => $payload['subjects_total'],
        ]);
    }

    /**
     * Test user can create a consent coverage without snapshot_id.
     */
    public function test_user_can_create_consent_coverage_without_snapshot(): void
    {
        $payload = $this->validPayload(['snapshot_id' => null]);
        unset($payload['snapshot_id']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Consent coverage created successfully',
            ]);
    }

    /**
     * Test create validates dataset_id is required.
     */
    public function test_create_validates_dataset_id_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['dataset_id']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('dataset_id');
    }

    /**
     * Test create validates dataset_id exists.
     */
    public function test_create_validates_dataset_id_exists(): void
    {
        $payload = $this->validPayload(['dataset_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('dataset_id');
    }

    /**
     * Test create validates snapshot_id exists if provided.
     */
    public function test_create_validates_snapshot_id_exists(): void
    {
        $payload = $this->validPayload(['snapshot_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('snapshot_id');
    }

    /**
     * Test create validates purpose is required.
     */
    public function test_create_validates_purpose_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['purpose']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('purpose');
    }

    /**
     * Test create validates purpose is valid enum.
     */
    public function test_create_validates_purpose_enum(): void
    {
        $payload = $this->validPayload(['purpose' => 'invalid_purpose']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('purpose');
    }

    /**
     * Test create validates jurisdiction is required.
     */
    public function test_create_validates_jurisdiction_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['jurisdiction']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jurisdiction');
    }

    /**
     * Test create validates jurisdiction is valid enum.
     */
    public function test_create_validates_jurisdiction_enum(): void
    {
        $payload = $this->validPayload(['jurisdiction' => 'INVALID']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jurisdiction');
    }

    /**
     * Test create validates as_of is required.
     */
    public function test_create_validates_as_of_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['as_of']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('as_of');
    }

    /**
     * Test create validates as_of is valid date.
     */
    public function test_create_validates_as_of_is_date(): void
    {
        $payload = $this->validPayload(['as_of' => 'not-a-date']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('as_of');
    }

    /**
     * Test create validates subjects_total is required.
     */
    public function test_create_validates_subjects_total_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['subjects_total']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_total');
    }

    /**
     * Test create validates subjects_total is integer.
     */
    public function test_create_validates_subjects_total_is_integer(): void
    {
        $payload = $this->validPayload(['subjects_total' => 'not-a-number']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_total');
    }

    /**
     * Test create validates subjects_total is not negative.
     */
    public function test_create_validates_subjects_total_min_zero(): void
    {
        $payload = $this->validPayload(['subjects_total' => -100]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_total');
    }

    /**
     * Test create validates subjects_with_valid_consent is required.
     */
    public function test_create_validates_subjects_with_valid_consent_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['subjects_with_valid_consent']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_with_valid_consent');
    }

    /**
     * Test create validates subjects_with_valid_consent is not negative.
     */
    public function test_create_validates_subjects_with_valid_consent_min_zero(): void
    {
        $payload = $this->validPayload(['subjects_with_valid_consent' => -50]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_with_valid_consent');
    }

    /**
     * Test create validates subjects_with_valid_consent does not exceed subjects_total.
     */
    public function test_create_validates_subjects_with_consent_not_exceeding_total(): void
    {
        $payload = $this->validPayload([
            'subjects_total' => 1000,
            'subjects_with_valid_consent' => 1500,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_with_valid_consent');
    }

    /**
     * Test create validates coverage_pct is required.
     */
    public function test_create_validates_coverage_pct_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['coverage_pct']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('coverage_pct');
    }

    /**
     * Test create validates coverage_pct is numeric.
     */
    public function test_create_validates_coverage_pct_is_numeric(): void
    {
        $payload = $this->validPayload(['coverage_pct' => 'not-a-number']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('coverage_pct');
    }

    /**
     * Test create validates coverage_pct min value.
     */
    public function test_create_validates_coverage_pct_min_zero(): void
    {
        $payload = $this->validPayload(['coverage_pct' => -10]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('coverage_pct');
    }

    /**
     * Test create validates coverage_pct max value.
     */
    public function test_create_validates_coverage_pct_max_hundred(): void
    {
        $payload = $this->validPayload(['coverage_pct' => 150]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('coverage_pct');
    }

    /**
     * Test create validates evidence_ref is required.
     */
    public function test_create_validates_evidence_ref_required(): void
    {
        $payload = $this->validPayload(['evidence_ref' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('evidence_ref');
    }

    /**
     * Test user can show a specific consent coverage.
     */
    public function test_user_can_show_specific_consent_coverage(): void
    {
        $coverage = ConsentCoverage::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/consent-coverages/{$coverage->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Consent coverage retrieved successfully',
                'data' => [
                    'id' => $coverage->id,
                    'dataset_id' => $coverage->dataset_id,
                ]
            ]);
    }

    /**
     * Test user can update a consent coverage.
     */
    public function test_user_can_update_consent_coverage(): void
    {
        $coverage = ConsentCoverage::factory()->create([
            'subjects_total' => 1000,
            'subjects_with_valid_consent' => 800,
            'coverage_pct' => 80.00,
        ]);

        $updatePayload = [
            'subjects_total' => 1200,
            'subjects_with_valid_consent' => 1100,
            'coverage_pct' => 91.67,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/consent-coverages/{$coverage->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Consent coverage updated successfully',
                'data' => [
                    'id' => $coverage->id,
                    'subjects_total' => 1200,
                    'subjects_with_valid_consent' => 1100,
                    'coverage_pct' => 91.67,
                ]
            ]);

        $this->assertDatabaseHas('consent_coverages', [
            'id' => $coverage->id,
            'subjects_total' => 1200,
        ]);
    }

    /**
     * Test user can partially update consent coverage.
     */
    public function test_user_can_partially_update_consent_coverage(): void
    {
        $coverage = ConsentCoverage::factory()->create([
            'purpose' => ConsentPurpose::MARKETING->value,
            'coverage_pct' => 75.00,
        ]);

        $updatePayload = [
            'coverage_pct' => 85.54,
        ];


        $response = $this->actingAs($this->user)->postJson("/api/consent-coverages/{$coverage->id}", $updatePayload);
        $response->assertStatus(200)
            ->assertJsonPath('data.coverage_pct', 85.54)
            ->assertJsonPath('data.purpose', ConsentPurpose::MARKETING->value);
    }

    /**
     * Test user can delete a consent coverage.
     */
    public function test_user_can_delete_consent_coverage(): void
    {
        $coverage = ConsentCoverage::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/consent-coverages/{$coverage->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Consent coverage deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('consent_coverages', ['id' => $coverage->id]);
    }

    /**
     * Test unauthenticated user cannot access index.
     */
    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/consent-coverages');

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot create coverage.
     */
    public function test_unauthenticated_user_cannot_create_coverage(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot update coverage.
     */
    public function test_unauthenticated_user_cannot_update_coverage(): void
    {
        $coverage = ConsentCoverage::factory()->create();

        $response = $this->postJson("/api/consent-coverages/{$coverage->id}", [
            'coverage_pct' => 95.00,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot delete coverage.
     */
    public function test_unauthenticated_user_cannot_delete_coverage(): void
    {
        $coverage = ConsentCoverage::factory()->create();

        $response = $this->deleteJson("/api/consent-coverages/{$coverage->id}");

        $response->assertStatus(401);
    }

    /**
     * Test show returns 404 for non-existent coverage.
     */
    public function test_show_returns_404_for_non_existent_coverage(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/consent-coverages/999999');

        $response->assertStatus(404);
    }

    /**
     * Test create handles all consent purposes.
     */
    public function test_create_handles_all_consent_purposes(): void
    {
        $purposes = [
            ConsentPurpose::MARKETING,
            ConsentPurpose::ANALYTICS,
            ConsentPurpose::PERSONALIZATION,
            ConsentPurpose::TRAINING_AI,
            ConsentPurpose::SERVICE_OPERATIONS,
        ];

        foreach ($purposes as $purpose) {
            $payload = $this->validPayload(['purpose' => $purpose->value]);
            $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);
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
            $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);
            $response->assertStatus(201);
        }
    }

    /**
     * Test create handles large subject numbers.
     */
    public function test_create_handles_large_numbers(): void
    {
        $payload = $this->validPayload([
            'subjects_total' => 10000000,
            'subjects_with_valid_consent' => 9500000,
            'coverage_pct' => 95.00,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/consent-coverages', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.subjects_total', 10000000)
            ->assertJsonPath('data.subjects_with_valid_consent', 9500000);
    }
}
