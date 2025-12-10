<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DataSubjectRequestAccess;
use App\Enums\DataSubjectRequestAccess\Status;
use App\Enums\DataSubjectRequestAccess\Priority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\DataSubjectRequestAccess\RequestType;
use App\Enums\DataSubjectRequestAccess\SubjectRealm;
use App\Enums\DataSubjectRequestAccess\RequestSource;
use App\Enums\DataSubjectRequestAccess\VerificationMethod;
use App\Enums\DataSubjectRequestAccess\VerificationStatus;

class DataSubjectRequestAccessControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test listing data subject requests with default pagination
     */
    public function test_index_returns_paginated_data_subject_requests(): void
    {
        DataSubjectRequestAccess::factory(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/data-subject-request-accesses');

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Data Subject Requests retrieved successfully');
    }

    /**
     * Test listing data subject requests with status filter
     */
    public function test_index_filters_by_status(): void
    {
        DataSubjectRequestAccess::factory(5)->create(['status' => Status::NEW->value]);
        DataSubjectRequestAccess::factory(3)->create(['status' => Status::COMPLETED->value]);

        $response = $this->actingAs($this->user)->getJson('/api/data-subject-request-accesses?status=completed');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data.data');
    }

    /**
     * Test listing data subject requests with request_type filter
     */
    public function test_index_filters_by_request_type(): void
    {
        DataSubjectRequestAccess::factory(4)->create(['request_type' => RequestType::ACCESS->value]);
        DataSubjectRequestAccess::factory(2)->create(['request_type' => RequestType::ERASURE->value]);

        $response = $this->actingAs($this->user)->getJson('/api/data-subject-request-accesses?request_type=erasure');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    /**
     * Test listing data subject requests with verification_status filter
     */
    public function test_index_filters_by_verification_status(): void
    {
        DataSubjectRequestAccess::factory(3)->create(['verification_status' => VerificationStatus::PENDING->value]);
        DataSubjectRequestAccess::factory(2)->create(['verification_status' => VerificationStatus::VERIFIED->value]);

        $response = $this->actingAs($this->user)->getJson('/api/data-subject-request-accesses?verification_status=verified');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    /**
     * Test listing data subject requests with priority filter
     */
    public function test_index_filters_by_priority(): void
    {
        DataSubjectRequestAccess::factory(3)->create(['priority' => Priority::LOW->value]);
        DataSubjectRequestAccess::factory(2)->create(['priority' => Priority::URGENT->value]);

        $response = $this->actingAs($this->user)->getJson('/api/data-subject-request-accesses?priority=urgent');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    /**
     * Test listing data subject requests with jurisdiction filter
     */
    public function test_index_filters_by_jurisdiction(): void
    {
        DataSubjectRequestAccess::factory(5)->create(['jurisdiction' => 'eu']);
        DataSubjectRequestAccess::factory(2)->create(['jurisdiction' => 'ksa']);

        $response = $this->actingAs($this->user)->getJson('/api/data-subject-request-accesses?jurisdiction=ksa');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    /**
     * Test listing data subject requests with subject_realm filter
     */
    public function test_index_filters_by_subject_realm(): void
    {
        DataSubjectRequestAccess::factory(4)->create(['subject_realm' => SubjectRealm::CUSTOMER->value]);
        DataSubjectRequestAccess::factory(2)->create(['subject_realm' => SubjectRealm::EMPLOYEE->value]);

        $response = $this->actingAs($this->user)->getJson('/api/data-subject-request-accesses?subject_realm=employee');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    /**
     * Test listing data subject requests with pagination
     */
    public function test_index_paginates_correctly(): void
    {
        DataSubjectRequestAccess::factory(25)->create();

        $response = $this->actingAs($this->user)->getJson('/api/data-subject-request-accesses?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data.data');
    }

    /**
     * Test creating a data subject request access with all fields
     */
    public function test_store_creates_data_subject_request_access_with_all_fields(): void
    {
        $data = [
            'request_type' => RequestType::ACCESS->value,
            'subject_identifier' => 'user@example.com',
            'subject_name' => 'John Doe',
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'verification_status' => VerificationStatus::VERIFIED->value,
            'subject_key' => 'subj_123',
            'verification_method' => VerificationMethod::EMAIL_LINK->value,
            'verified_by' => $this->user->id,
            'request_details' => 'Please provide all my personal data',
            'requested_data_categories' => ['email', 'phone'],
            'request_source' => RequestSource::EMAIL->value,
            'submitted_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => Status::NEW->value,
            'priority' => Priority::NORMAL->value,
            'is_overdue' => false,
            'assigned_to' => $this->user->id,
            'assigned_date' => now()->toDateString(),
            'jurisdiction' => 'EU',
            'systems_checked' => 'CRM',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-subject-request-accesses', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Data Subject Request Access created successfully');

        $dsr = DataSubjectRequestAccess::where('subject_identifier', 'user@example.com')->first();
        $this->assertNotNull($dsr);
        $this->assertEquals(RequestType::ACCESS->value, $dsr->request_type);
        $this->assertEquals('John Doe', $dsr->subject_name);
    }

    /**
     * Test creating a data subject request auto-generates request_code
     */
    public function test_store_auto_generates_request_code(): void
    {
        $data = [
            'request_type' => RequestType::ACCESS->value,
            'subject_identifier' => 'test@example.com',
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'verification_status' => VerificationStatus::PENDING->value,
            'request_details' => 'Test request',
            'request_source' => RequestSource::EMAIL->value,
            'submitted_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => Status::NEW->value,
            'priority' => Priority::NORMAL->value,
            'is_overdue' => false,
            'assigned_to' => $this->user->id,
            'assigned_date' => now()->toDateString(),
            'jurisdiction' => 'EU',
            'systems_checked' => 'CRM',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-subject-request-accesses', $data);

        $response->assertStatus(201);

        $dsr = DataSubjectRequestAccess::where('subject_identifier', 'test@example.com')->first();
        $this->assertNotNull($dsr->request_code);
        $this->assertStringStartsWith('DSAR-'.date('Y'), $dsr->request_code);
    }

    /**
     * Test creating a data subject request sets verification_date when verified
     */
    public function test_store_sets_verification_date_when_verified(): void
    {
        $data = [
            'request_type' => RequestType::ACCESS->value,
            'subject_identifier' => 'verified@example.com',
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'verification_status' => VerificationStatus::VERIFIED->value,
            'subject_key' => 'subj_key',
            'verification_method' => VerificationMethod::EMAIL_LINK->value,
            'verified_by' => $this->user->id,
            'request_details' => 'Test request',
            'request_source' => RequestSource::EMAIL->value,
            'submitted_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => Status::NEW->value,
            'priority' => Priority::NORMAL->value,
            'is_overdue' => false,
            'assigned_to' => $this->user->id,
            'assigned_date' => now()->toDateString(),
            'jurisdiction' => 'EU',
            'systems_checked' => 'CRM',

        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-subject-request-accesses', $data);
        $response->assertStatus(201);

        $response = $response->json();
        $this->assertNotNull($response['data']['verification_date']);
    }

    /**
     * Test creating a data subject request with validation errors
     */
    public function test_store_validates_required_fields(): void
    {
        $data = [
            'subject_identifier' => 'test@example.com',
            // Missing required fields
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-subject-request-accesses', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'request_type',
            'subject_realm',
            'verification_status',
            'request_details',
            'request_source',
            'submitted_date',
            'due_date',
            'status',
            'priority',
            'is_overdue',
            'assigned_to',
            'assigned_date',
        ]);
    }

    /**
     * Test creating a data subject request with invalid enum values
     */
    public function test_store_validates_enum_values(): void
    {
        $data = [
            'request_type' => 'invalid_type',
            'subject_identifier' => 'test@example.com',
            'subject_realm' => 'invalid_realm',
            'verification_status' => 'invalid_status',
            'request_details' => 'Test',
            'request_source' => 'invalid_source',
            'submitted_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'invalid_status',
            'priority' => 'invalid_priority',
            'is_overdue' => false,
            'assigned_to' => $this->user->id,
            'assigned_date' => now()->toDateString(),
            'jurisdiction' => 'EU',
            'systems_checked' => ['CRM'],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-subject-request-accesses', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'request_type',
            'subject_realm',
            'verification_status',
            'request_source',
            'status',
            'priority',
        ]);
    }

    /**
     * Test showing a data subject request
     */
    public function test_show_returns_data_subject_request(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/data-subject-request-accesses/{$dsr->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('data.id', $dsr->id);
        $response->assertJsonPath('data.request_code', $dsr->request_code);
    }

    /**
     * Test showing a non-existent data subject request returns 404
     */
    public function test_show_returns_404_for_non_existent_request(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/data-subject-request-accesses/99999');

        $response->assertStatus(404);
    }

    /**
     * Test updating a data subject request
     */
    public function test_update_modifies_data_subject_request(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create([
            'status' => Status::NEW->value,
            'priority' => Priority::LOW->value,
        ]);

        $data = [
            'status' => Status::IN_PROGRESS->value,
            'priority' => Priority::HIGH->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/data-subject-request-accesses/{$dsr->id}", $data);

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Data Subject Request Access updated successfully');

        $updated = $dsr->fresh();
        $this->assertEquals(Status::IN_PROGRESS->value, $updated->status);
        $this->assertEquals(Priority::HIGH->value, $updated->priority);
    }

    /**
     * Test updating a data subject request sets verification_date when changing to verified
     */
    public function test_update_sets_verification_date_when_changing_to_verified(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create([
            'verification_status' => VerificationStatus::PENDING->value,
            'verification_date' => null,
        ]);

        $data = [
            'verification_status' => VerificationStatus::VERIFIED->value,
            'subject_key' => 'subj_key_updated',
            'verification_method' => VerificationMethod::EMAIL_LINK->value,
            'verified_by' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/data-subject-request-accesses/{$dsr->id}", $data);

        $response->assertStatus(200);

        $updated = $dsr->fresh();
        $this->assertNotNull($updated->verification_date);
        $this->assertEquals(VerificationStatus::VERIFIED->value, $updated->verification_status);
    }

    /**
     * Test updating a data subject request with partial data
     */
    public function test_update_with_partial_data(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create([
            'status' => Status::NEW->value,
            'priority' => Priority::LOW->value,
            'request_type' => RequestType::ACCESS->value,
        ]);

        $data = ['status' => Status::IN_PROGRESS->value];

        $response = $this->actingAs($this->user)->postJson("/api/data-subject-request-accesses/{$dsr->id}", $data);

        $response->assertStatus(200);

        $updated = $dsr->fresh();
        $this->assertEquals(Status::IN_PROGRESS->value, $updated->status);
        $this->assertEquals(Priority::LOW->value, $updated->priority);
        $this->assertEquals(RequestType::ACCESS->value, $updated->request_type);
    }

    /**
     * Test updating a data subject request with validation errors
     */
    public function test_update_validates_enum_values(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create();

        $data = [
            'status' => 'invalid_status',
            'priority' => 'invalid_priority',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/data-subject-request-accesses/{$dsr->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status', 'priority']);
    }

    /**
     * Test deleting a data subject request
     */
    public function test_destroy_deletes_data_subject_request(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create();
        $id = $dsr->id;

        $response = $this->actingAs($this->user)->deleteJson("/api/data-subject-request-accesses/{$dsr->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Data Subject Request Access deleted successfully');

        $this->assertDatabaseMissing('data_subject_request_accesses', ['id' => $id]);
    }

    /**
     * Test deleting a non-existent data subject request returns 404
     */
    public function test_destroy_returns_404_for_non_existent_request(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/data-subject-request-accesses/99999');

        $response->assertStatus(404);
    }
}
