<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ConsentRecord;
use App\Enums\ConsentRecord\Method;
use App\Enums\ConsentRecord\Status;
use App\Enums\ConsentRecord\Purpose;
use App\Enums\ConsentRecord\Lifecycle;
use App\Enums\ConsentRecord\SourceSystem;
use App\Enums\ConsentRecord\SubjectRealm;
use App\Models\RecordOfProcessingActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\RecordOfProcessingActivity\DataCategory;

class ConsentRecordControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test listing consent records
     */
    public function test_index_returns_paginated_consent_records(): void
    {
        ConsentRecord::factory(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/consent-records');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'data' => [
                'data',
            ],
            'message',
        ]);
        $response->assertJsonFragment(['error' => false]);
    }

    /**
     * Test listing consent records with status filter
     */
    public function test_index_filters_by_status(): void
    {
        ConsentRecord::factory(5)->create(['status' => Status::GRANTED->value]);
        ConsentRecord::factory(3)->create(['status' => Status::DENIED->value]);

        $response = $this->actingAs($this->user)->getJson('/api/consent-records?status=granted');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data.data');
    }

    /**
     * Test listing consent records with lifecycle_stage filter
     */
    public function test_index_filters_by_lifecycle_stage(): void
    {
        ConsentRecord::factory(4)->create(['lifecycle_stage' => Lifecycle::OBTAINED->value]);
        ConsentRecord::factory(2)->create(['lifecycle_stage' => Lifecycle::WITHDRAWN->value]);

        $response = $this->actingAs($this->user)->getJson('/api/consent-records?lifecycle_stage=obtained');

        $response->assertStatus(200);
        $response->assertJsonCount(4, 'data.data');
    }

    /**
     * Test listing consent records with language filter
     */
    public function test_index_filters_by_language(): void
    {
        ConsentRecord::factory(3)->create(['language' => 'en']);
        ConsentRecord::factory(2)->create(['language' => 'ar']);

        $response = $this->actingAs($this->user)->getJson('/api/consent-records?language=ar');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    /**
     * Test listing consent records with jurisdiction filter
     */
    public function test_index_filters_by_jurisdiction(): void
    {
        ConsentRecord::factory(3)->create(['jurisdiction' => 'eu']);
        ConsentRecord::factory(2)->create(['jurisdiction' => 'uae']);

        $response = $this->actingAs($this->user)->getJson('/api/consent-records?jurisdiction=eu');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data.data');
    }

    /**
     * Test listing consent records with custom per_page
     */
    public function test_index_with_custom_per_page(): void
    {
        ConsentRecord::factory(30)->create();

        $response = $this->actingAs($this->user)->getJson('/api/consent-records?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data.data');
    }

    /**
     * Test storing a consent record with valid data
     */
    public function test_store_creates_consent_record(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = [
            'subject_key' => 'subject-123',
            'subject_realm' => SubjectRealm::EMPLOYEE->value,
            'subject_age_group' => '18-25',
            'purpose' => Purpose::PROFILING->value,
            'record_of_processing_activity_id' => $ropa->id,
            'status' => Status::GRANTED->value,
            'lifecycle_stage' => Lifecycle::ACTIVE->value,
            'consent_version' => 1,
            'consent_text' => 'I agree to the terms',
            'consent_method' => Method::PRE_CHECKED->value,
            'effective_from' => now()->toDateString(),
            'source_system' => SourceSystem::WEBSITE->value,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'language' => 'en',
            'jurisdiction' => 'eu',
            'data_categories' => [DataCategory::FINANCIAL->value],
            'can_withdraw' => true,
            'withdrawal_method' => 'email',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/consent-records', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment(['error' => false]);
        $response->assertJsonFragment(['message' => 'Consent record created successfully']);
        $this->assertDatabaseHas('consent_records', ['subject_key' => 'subject-123']);
    }

    /**
     * Test storing consent record with minimal required data
     */
    public function test_store_creates_consent_record_with_minimal_data(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = [
            'subject_key' => 'subject-456',
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'purpose' => Purpose::MARKETING->value,
            'record_of_processing_activity_id' => $ropa->id,
            'status' => Status::GRANTED->value,
            'lifecycle_stage' => Lifecycle::ACTIVE->value,
            'consent_version' => 1,
            'consent_text' => 'Minimal consent',
            'consent_method' => Method::WRITTEN->value,
            'effective_from' => now()->toDateString(),
            'source_system' => SourceSystem::PORTAL->value,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'API Client',
            'language' => 'en',
            'jurisdiction' => 'uae',
            'data_categories' => [DataCategory::SENSITIVE->value],
            'can_withdraw' => false,
            'withdrawal_method' => 'api',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/consent-records', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('consent_records', ['subject_key' => 'subject-456']);
    }

    /**
     * Test storing consent record validation - missing required field
     */
    public function test_store_fails_without_required_field(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = [
            'subject_realm' => 'individual',
            'record_of_processing_activity_id' => $ropa->id,
            'status' => 'active',
            'lifecycle_stage' => 'development',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/consent-records', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['subject_key']);
    }

    /**
     * Test storing consent record with invalid enum value
     */
    public function test_store_fails_with_invalid_enum_value(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = ConsentRecord::factory()->make([
            'record_of_processing_activity_id' => $ropa->id,
            'status' => 'invalid_status',
        ])->toArray();

        $response = $this->actingAs($this->user)->postJson('/api/consent-records', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /**
     * Test storing consent record with invalid IP address
     */
    public function test_store_fails_with_invalid_ip(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = ConsentRecord::factory()->make([
            'record_of_processing_activity_id' => $ropa->id,
            'ip_address' => 'not-an-ip',
        ])->toArray();

        $response = $this->actingAs($this->user)->postJson('/api/consent-records', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ip_address']);
    }

    /**
     * Test storing consent record with non-existent ROPA
     */
    public function test_store_fails_with_non_existent_ropa(): void
    {
        $data = ConsentRecord::factory()->make([
            'record_of_processing_activity_id' => 9999,
        ])->toArray();

        $response = $this->actingAs($this->user)->postJson('/api/consent-records', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['record_of_processing_activity_id']);
    }

    /**
     * Test showing a consent record
     */
    public function test_show_returns_consent_record(): void
    {
        $consentRecord = ConsentRecord::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/consent-records/{$consentRecord->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'error' => false,
            'message' => 'Consent record retrieved successfully',
        ]);
        $response->assertJsonFragment(['id' => $consentRecord->id]);
    }

    /**
     * Test showing non-existent consent record
     */
    public function test_show_returns_404_for_non_existent_record(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/consent-records/9999');

        $response->assertStatus(404);
    }

    /**
     * Test updating a consent record
     */
    public function test_update_consent_record(): void
    {
        $consentRecord = ConsentRecord::factory()->create([
            'status' => Status::GRANTED->value,
            'can_withdraw' => true,
        ]);

        $data = [
            'status' => Status::DENIED->value,
            'can_withdraw' => false,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/consent-records/{$consentRecord->id}", $data);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'error' => false,
            'message' => 'Consent record updated successfully',
        ]);
        $this->assertDatabaseHas('consent_records', [
            'id' => $consentRecord->id,
            'status' => Status::DENIED->value,
            'can_withdraw' => false,
        ]);
    }

    /**
     * Test updating consent record with invalid enum
     */
    public function test_update_fails_with_invalid_enum(): void
    {
        $consentRecord = ConsentRecord::factory()->create();

        $data = ['status' => 'invalid_status'];

        $response = $this->actingAs($this->user)->postJson("/api/consent-records/{$consentRecord->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /**
     * Test updating non-existent consent record
     */
    public function test_update_non_existent_record_returns_404(): void
    {
        $data = ['status' => 'active'];

        $response = $this->actingAs($this->user)->postJson('/api/consent-records/9999', $data);

        $response->assertStatus(404);
    }

    /**
     * Test deleting a consent record
     */
    public function test_delete_consent_record(): void
    {
        $consentRecord = ConsentRecord::factory()->create();
        $id = $consentRecord->id;

        $response = $this->actingAs($this->user)->deleteJson("/api/consent-records/{$id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'error' => false,
            'message' => 'Consent record deleted successfully',
        ]);
        $this->assertDatabaseMissing('consent_records', ['id' => $id]);
    }

    /**
     * Test deleting non-existent consent record
     */
    public function test_delete_non_existent_record_returns_404(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/consent-records/9999');

        $response->assertStatus(404);
    }

    /**
     * Test listing consent records without authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/consent-records');

        $response->assertStatus(401);
    }

    /**
     * Test storing consent record without authentication
     */
    public function test_store_requires_authentication(): void
    {
        $data = ConsentRecord::factory()->make()->toArray();

        $response = $this->postJson('/api/consent-records', $data);

        $response->assertStatus(401);
    }

    /**
     * Test showing consent record without authentication
     */
    public function test_show_requires_authentication(): void
    {
        $consentRecord = ConsentRecord::factory()->create();

        $response = $this->getJson("/api/consent-records/{$consentRecord->id}");

        $response->assertStatus(401);
    }

    /**
     * Test updating consent record without authentication
     */
    public function test_update_requires_authentication(): void
    {
        $consentRecord = ConsentRecord::factory()->create();
        $data = ['status' => 'active'];

        $response = $this->postJson("/api/consent-records/{$consentRecord->id}", $data);

        $response->assertStatus(401);
    }

    /**
     * Test deleting consent record without authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $consentRecord = ConsentRecord::factory()->create();

        $response = $this->deleteJson("/api/consent-records/{$consentRecord->id}");

        $response->assertStatus(401);
    }

    /**
     * Test data_categories validation - array with enum values
     */
    public function test_store_validates_data_categories_as_array(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = ConsentRecord::factory()->make([
            'record_of_processing_activity_id' => $ropa->id,
            'data_categories' => 'not-an-array',
        ])->toArray();

        $response = $this->actingAs($this->user)->postJson('/api/consent-records', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['data_categories']);
    }

    /**
     * Test response structure
     */
    public function test_response_structure_is_consistent(): void
    {
        $consentRecord = ConsentRecord::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/consent-records/{$consentRecord->id}");

        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'consent_code',
                'subject_key',
                'subject_realm',
                'status',
                'lifecycle_stage',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
