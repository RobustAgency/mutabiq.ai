<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\RecordOfProcessingActivity;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecordOfProcessingActivityControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'activity_name' => 'Customer Data Processing',
            'purpose' => 'Customer analytics and service improvement',
            'detailed_purpose' => 'Process customer transaction data to improve service offerings',
            'owner_team' => 'it',
            'controller_role' => 'controller',
            'data_subject_categories' => ['customers', 'prospects'],
            'data_categories' => ['contact', 'financial'],
            'contains_pii' => true,
            'consent_required' => true,
            'lawful_basis' => 'consent',
            'legitimate_interest_assessment' => 'Balancing test completed',
            'consent_coverage_percent' => 95,
            'retention_period' => '2 years',
            'retention_justification' => 'Required for compliance',
            'has_international_transfers' => true,
            'applicable_jurisdictions' => ['eu', 'uk'],
            'security_measures' => 'AES-256 encryption',
            'internal_recipients' => ['Analytics Team'],
            'external_recipients' => ['Cloud Provider'],
            'status' => 'active',
            'last_reviewed_date' => now()->format('Y-m-d'),
            'next_review_date' => now()->addMonths(6)->format('Y-m-d'),
        ], $overrides);
    }

    /**
     * Test user can list processing activities.
     */
    public function test_user_can_list_processing_activities(): void
    {
        RecordOfProcessingActivity::factory()->count(5)->create();

        $response = $this->actingAs($this->user)->getJson('/api/record-of-processing-activities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'activity_code',
                            'activity_name',
                            'purpose',
                            'owner_team',
                            'status',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ])
            ->assertJson(['error' => false, 'message' => 'Processing activities retrieved successfully']);
    }

    /**
     * Test user can list with custom per_page.
     */
    public function test_user_can_list_with_custom_per_page(): void
    {
        RecordOfProcessingActivity::factory()->count(25)->create();

        $response = $this->actingAs($this->user)->getJson('/api/record-of-processing-activities?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    /**
     * Test user can filter by status.
     */
    public function test_user_can_filter_by_status(): void
    {
        RecordOfProcessingActivity::factory()->count(3)->create(['status' => 'active']);
        RecordOfProcessingActivity::factory()->count(2)->create(['status' => 'draft']);

        $response = $this->actingAs($this->user)->getJson('/api/record-of-processing-activities?status=active');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    /**
     * Test user can filter by owner_team.
     */
    public function test_user_can_filter_by_owner_team(): void
    {
        RecordOfProcessingActivity::factory()->count(3)->create(['owner_team' => 'it']);
        RecordOfProcessingActivity::factory()->count(2)->create(['owner_team' => 'hr']);

        $response = $this->actingAs($this->user)->getJson('/api/record-of-processing-activities?owner_team=it');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    /**
     * Test user can create activity with minimal fields.
     */
    public function test_user_can_create_activity_with_minimal_fields(): void
    {
        $payload = [
            'activity_name' => 'Minimal Activity',
            'purpose' => 'Testing',
            'owner_team' => 'finance',
            'controller_role' => 'processor',
            'data_subject_categories' => ['employees'],
            'data_categories' => ['identifier'],
            'contains_pii' => false,
            'consent_required' => false,
            'lawful_basis' => 'contract',
            'consent_coverage_percent' => 0,
            'dpia_required' => false,
            'dpia_status' => 'required',
            'retention_period' => '1 year',
            'retention_justification' => 'Compliance requirement',
            'has_international_transfers' => false,
            'applicable_jurisdictions' => ['eu'],
            'security_measures' => 'Standard security',
            'internal_recipients' => [],
            'external_recipients' => [],
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(201)
            ->assertJson(['error' => false]);
    }

    /**
     * Test create validates activity_name is required.
     */
    public function test_create_validates_activity_name_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['activity_name']);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('activity_name');
    }

    /**
     * Test create validates purpose is required.
     */
    public function test_create_validates_purpose_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['purpose']);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('purpose');
    }

    /**
     * Test create validates owner_team enum.
     */
    public function test_create_validates_owner_team_enum(): void
    {
        $payload = $this->validPayload(['owner_team' => 'invalid_team']);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('owner_team');
    }

    /**
     * Test create validates controller_role enum.
     */
    public function test_create_validates_controller_role_enum(): void
    {
        $payload = $this->validPayload(['controller_role' => 'invalid_role']);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

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

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('data_subject_categories');
    }

    /**
     * Test create validates data_subject_categories is array.
     */
    public function test_create_validates_data_subject_categories_is_array(): void
    {
        $payload = $this->validPayload(['data_subject_categories' => 'not-array']);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('data_subject_categories');
    }

    /**
     * Test create validates data_subject_categories min 1.
     */
    public function test_create_validates_data_subject_categories_min_one(): void
    {
        $payload = $this->validPayload(['data_subject_categories' => []]);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('data_subject_categories');
    }

    /**
     * Test create validates data_categories is required.
     */
    public function test_create_validates_data_categories_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['data_categories']);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('data_categories');
    }

    /**
     * Test create validates lawful_basis enum.
     */
    public function test_create_validates_lawful_basis_enum(): void
    {
        $payload = $this->validPayload(['lawful_basis' => 'invalid_basis']);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('lawful_basis');
    }

    /**
     * Test create validates dpia_status enum.
     */
    public function test_create_validates_dpia_status_enum(): void
    {
        $payload = $this->validPayload(['dpia_status' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('dpia_status');
    }

    /**
     * Test create validates applicable_jurisdictions enum.
     */
    public function test_create_validates_applicable_jurisdictions_enum(): void
    {
        $payload = $this->validPayload(['applicable_jurisdictions' => ['eu', 'invalid_jurisdiction']]);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('applicable_jurisdictions.1');
    }

    /**
     * Test create validates status enum.
     */
    public function test_create_validates_status_enum(): void
    {
        $payload = $this->validPayload(['status' => 'invalid_status']);

        $response = $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * Test user can retrieve activity by id.
     */
    public function test_user_can_retrieve_activity_by_id(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/record-of-processing-activities/{$activity->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'activity_code',
                    'activity_name',
                    'purpose',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Processing activity retrieved successfully',
                'data' => [
                    'id' => $activity->id,
                    'activity_code' => $activity->activity_code,
                ],
            ]);
    }

    /**
     * Test user gets 404 when retrieving non-existent activity.
     */
    public function test_user_gets_404_when_retrieving_non_existent_activity(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/record-of-processing-activities/99999');

        $response->assertStatus(404);
    }

    /**
     * Test user can update activity with all fields.
     */
    public function test_user_can_update_activity_with_all_fields(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create(['status' => 'draft']);
        $updates = [
            'status' => 'active',
            'dpia_status' => 'approved',
            'next_review_date' => now()->addMonths(12)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)->postJson("/api/record-of-processing-activities/{$activity->id}", $updates);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Processing activity updated successfully',
                'data' => [
                    'id' => $activity->id,
                    'status' => 'active',
                ],
            ]);

        $this->assertDatabaseHas('record_of_processing_activities', [
            'id' => $activity->id,
            'status' => 'active',
            'dpia_status' => 'approved',
        ]);
    }

    /**
     * Test user can update activity with partial fields.
     */
    public function test_user_can_update_activity_with_partial_fields(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create();
        $originalCode = $activity->activity_code;

        $response = $this->actingAs($this->user)->postJson("/api/record-of-processing-activities/{$activity->id}", [
            'activity_name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson(['error' => false]);

        $activity->refresh();
        $this->assertEquals('Updated Name', $activity->activity_name);
        $this->assertEquals($originalCode, $activity->activity_code);
    }

    /**
     * Test update validates enum values.
     */
    public function test_update_validates_enum_values(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create();

        $response = $this->actingAs($this->user)->postJson(
            "/api/record-of-processing-activities/{$activity->id}",
            ['status' => 'invalid_status']
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    /**
     * Test user can delete activity.
     */
    public function test_user_can_delete_activity(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/record-of-processing-activities/{$activity->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Processing activity deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('record_of_processing_activities', [
            'id' => $activity->id,
        ]);
    }

    /**
     * Test user gets 404 when deleting non-existent activity.
     */
    public function test_user_gets_404_when_deleting_non_existent_activity(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/record-of-processing-activities/99999');

        $response->assertStatus(404);
    }

    /**
     * Test created_by and updated_by are set automatically on create.
     */
    public function test_created_by_and_updated_by_set_on_create(): void
    {
        $payload = $this->validPayload();

        $this->actingAs($this->user)->postJson('/api/record-of-processing-activities', $payload);

        $this->assertDatabaseHas('record_of_processing_activities', [
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
    }

    /**
     * Test unauthenticated user cannot access endpoints.
     */
    public function test_unauthenticated_user_cannot_access_endpoints(): void
    {
        $response = $this->getJson('/api/record-of-processing-activities');

        $response->assertStatus(401);
    }
}
