<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\DatasetSubjectPopulation;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatasetSubjectPopulationControllerTest extends TestCase
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

        return array_merge([
            'dataset_id' => $dataset->id,
            'snapshot_id' => DatasetSnapshot::factory()->for($dataset)->create(['organization_id' => $this->organization->id])->id,
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'jurisdiction' => Jurisdiction::EU->value,
            'subjects_total' => 10000,
            'as_of' => now()->format('Y-m-d H:i:s'),
        ], $overrides);
    }

    /**
     * Test user can get paginated dataset subject populations.
     */
    public function test_user_can_get_paginated_populations(): void
    {
        DatasetSubjectPopulation::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/dataset-subject-populations');

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
                            'subject_realm',
                            'jurisdiction',
                            'subjects_total',
                            'as_of',
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
     * Test user can get paginated populations with custom per_page.
     */
    public function test_user_can_get_paginated_populations_with_custom_per_page(): void
    {
        DatasetSubjectPopulation::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/dataset-subject-populations?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    /**
     * Test user can create a dataset subject population with all fields.
     */
    public function test_user_can_create_population_with_all_fields(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'dataset_id',
                    'snapshot_id',
                    'subject_realm',
                    'jurisdiction',
                    'subjects_total',
                ]
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Dataset subject population created successfully',
                'data' => [
                    'dataset_id' => $payload['dataset_id'],
                    'subject_realm' => $payload['subject_realm'],
                    'jurisdiction' => $payload['jurisdiction'],
                    'subjects_total' => $payload['subjects_total'],
                ]
            ]);

        $this->assertDatabaseHas('dataset_subject_populations', [
            'dataset_id' => $payload['dataset_id'],
            'subjects_total' => $payload['subjects_total'],
        ]);
    }

    /**
     * Test user can create a population without snapshot_id.
     */
    public function test_user_can_create_population_without_snapshot(): void
    {
        $payload = $this->validPayload(['snapshot_id' => null]);
        unset($payload['snapshot_id']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset subject population created successfully',
            ]);
    }

    /**
     * Test create validates dataset_id is required.
     */
    public function test_create_validates_dataset_id_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['dataset_id']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('dataset_id');
    }

    /**
     * Test create validates dataset_id exists.
     */
    public function test_create_validates_dataset_id_exists(): void
    {
        $payload = $this->validPayload(['dataset_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('dataset_id');
    }

    /**
     * Test create validates snapshot_id exists if provided.
     */
    public function test_create_validates_snapshot_id_exists(): void
    {
        $payload = $this->validPayload(['snapshot_id' => 999999]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('snapshot_id');
    }

    /**
     * Test create validates subject_realm is required.
     */
    public function test_create_validates_subject_realm_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['subject_realm']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subject_realm');
    }

    /**
     * Test create validates subject_realm is valid enum.
     */
    public function test_create_validates_subject_realm_enum(): void
    {
        $payload = $this->validPayload(['subject_realm' => 'invalid_realm']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subject_realm');
    }

    /**
     * Test create validates jurisdiction is required.
     */
    public function test_create_validates_jurisdiction_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['jurisdiction']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jurisdiction');
    }

    /**
     * Test create validates jurisdiction is valid enum.
     */
    public function test_create_validates_jurisdiction_enum(): void
    {
        $payload = $this->validPayload(['jurisdiction' => 'INVALID']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jurisdiction');
    }

    /**
     * Test create validates subjects_total is required.
     */
    public function test_create_validates_subjects_total_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['subjects_total']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_total');
    }

    /**
     * Test create validates subjects_total is integer.
     */
    public function test_create_validates_subjects_total_is_integer(): void
    {
        $payload = $this->validPayload(['subjects_total' => 'not-a-number']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_total');
    }

    /**
     * Test create validates subjects_total is not negative.
     */
    public function test_create_validates_subjects_total_min_zero(): void
    {
        $payload = $this->validPayload(['subjects_total' => -100]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_total');
    }

    /**
     * Test create validates as_of is required.
     */
    public function test_create_validates_as_of_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['as_of']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('as_of');
    }

    /**
     * Test create validates as_of is valid date.
     */
    public function test_create_validates_as_of_is_date(): void
    {
        $payload = $this->validPayload(['as_of' => 'not-a-date']);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('as_of');
    }

    /**
     * Test user can show a specific population.
     */
    public function test_user_can_show_specific_population(): void
    {
        $population = DatasetSubjectPopulation::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson("/api/dataset-subject-populations/{$population->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset subject population retrieved successfully',
                'data' => [
                    'id' => $population->id,
                    'dataset_id' => $population->dataset_id,
                ]
            ]);
    }

    /**
     * Test user can update a population.
     */
    public function test_user_can_update_population(): void
    {
        $population = DatasetSubjectPopulation::factory()->create([
            'organization_id' => $this->organization->id,
            'subjects_total' => 1000,
            'subject_realm' => SubjectRealm::CUSTOMER->value,
        ]);

        $updatePayload = [
            'subjects_total' => 2000,
            'subject_realm' => SubjectRealm::EMPLOYEE->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/dataset-subject-populations/{$population->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset subject population updated successfully',
                'data' => [
                    'id' => $population->id,
                    'subjects_total' => 2000,
                    'subject_realm' => SubjectRealm::EMPLOYEE->value,
                ]
            ]);

        $this->assertDatabaseHas('dataset_subject_populations', [
            'id' => $population->id,
            'subjects_total' => 2000,
        ]);
    }

    /**
     * Test user can partially update population.
     */
    public function test_user_can_partially_update_population(): void
    {
        $population = DatasetSubjectPopulation::factory()->create([
            'organization_id' => $this->organization->id,
            'subjects_total' => 1000,
            'jurisdiction' => Jurisdiction::EU->value,
        ]);

        $updatePayload = [
            'subjects_total' => 1500,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/dataset-subject-populations/{$population->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('data.subjects_total', 1500)
            ->assertJsonPath('data.jurisdiction', Jurisdiction::EU->value);
    }

    /**
     * Test user can delete a population.
     */
    public function test_user_can_delete_population(): void
    {
        $population = DatasetSubjectPopulation::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/dataset-subject-populations/{$population->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Dataset subject population deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('dataset_subject_populations', ['id' => $population->id]);
    }

    /**
     * Test unauthenticated user cannot access index.
     */
    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->getJson('/api/dataset-subject-populations');

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot create population.
     */
    public function test_unauthenticated_user_cannot_create_population(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot update population.
     */
    public function test_unauthenticated_user_cannot_update_population(): void
    {
        $population = DatasetSubjectPopulation::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->postJson("/api/dataset-subject-populations/{$population->id}", [
            'subjects_total' => 5000,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot delete population.
     */
    public function test_unauthenticated_user_cannot_delete_population(): void
    {
        $population = DatasetSubjectPopulation::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->deleteJson("/api/dataset-subject-populations/{$population->id}");

        $response->assertStatus(401);
    }

    /**
     * Test show returns 404 for non-existent population.
     */
    public function test_show_returns_404_for_non_existent_population(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/dataset-subject-populations/999999');

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
            $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);
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
            $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);
            $response->assertStatus(201);
        }
    }

    /**
     * Test create handles zero subjects.
     */
    public function test_create_handles_zero_subjects(): void
    {
        $payload = $this->validPayload(['subjects_total' => 0]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.subjects_total', 0);
    }

    /**
     * Test create handles large subject numbers.
     */
    public function test_create_handles_large_numbers(): void
    {
        $payload = $this->validPayload([
            'subjects_total' => 50000000,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.subjects_total', 50000000);
    }

    /**
     * Test create handles past as_of dates.
     */
    public function test_create_handles_past_as_of_dates(): void
    {
        $pastDate = now()->subMonths(6);
        $payload = $this->validPayload([
            'as_of' => $pastDate->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/dataset-subject-populations', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('dataset_subject_populations', [
            'dataset_id' => $payload['dataset_id'],
        ]);
    }

    /**
     * Test update validates subjects_total min value.
     */
    public function test_update_validates_subjects_total_min_zero(): void
    {
        $population = DatasetSubjectPopulation::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->postJson("/api/dataset-subject-populations/{$population->id}", [
            'subjects_total' => -50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subjects_total');
    }

    /**
     * Test update validates invalid subject_realm.
     */
    public function test_update_validates_invalid_subject_realm(): void
    {
        $population = DatasetSubjectPopulation::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->postJson("/api/dataset-subject-populations/{$population->id}", [
            'subject_realm' => 'invalid_realm',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('subject_realm');
    }

    /**
     * Test update validates invalid jurisdiction.
     */
    public function test_update_validates_invalid_jurisdiction(): void
    {
        $population = DatasetSubjectPopulation::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->postJson("/api/dataset-subject-populations/{$population->id}", [
            'jurisdiction' => 'INVALID',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jurisdiction');
    }
}
