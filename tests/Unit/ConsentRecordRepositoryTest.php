<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ConsentRecord;
use App\Models\RecordOfProcessingActivity;
use App\Repositories\ConsentRecordRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConsentRecordRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ConsentRecordRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ConsentRecordRepository::class);
    }

    /**
     * Test getting filtered consent records with pagination
     */
    public function test_get_filtered_consent_records_with_default_pagination(): void
    {
        ConsentRecord::factory(20)->create();

        $result = $this->repository->getFilteredConsentRecords();

        $this->assertCount(15, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test getting filtered consent records with custom per_page
     */
    public function test_get_filtered_consent_records_with_custom_per_page(): void
    {
        ConsentRecord::factory(30)->create();

        $result = $this->repository->getFilteredConsentRecords(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(30, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    /**
     * Test filtering by status
     */
    public function test_get_filtered_consent_records_by_status(): void
    {
        ConsentRecord::factory(5)->create(['status' => 'draft']);
        ConsentRecord::factory(3)->create(['status' => 'active']);

        $result = $this->repository->getFilteredConsentRecords(['status' => 'active']);

        $this->assertCount(3, $result->items());
        $this->assertTrue($result->items()[0]->status === 'active');
    }

    /**
     * Test filtering by lifecycle_stage
     */
    public function test_get_filtered_consent_records_by_lifecycle_stage(): void
    {
        ConsentRecord::factory(4)->create(['lifecycle_stage' => 'development']);
        ConsentRecord::factory(2)->create(['lifecycle_stage' => 'deployment']);

        $result = $this->repository->getFilteredConsentRecords(['lifecycle_stage' => 'deployment']);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->lifecycle_stage === 'deployment');
    }

    /**
     * Test filtering by language
     */
    public function test_get_filtered_consent_records_by_language(): void
    {
        ConsentRecord::factory(3)->create(['language' => 'en']);
        ConsentRecord::factory(2)->create(['language' => 'ar']);

        $result = $this->repository->getFilteredConsentRecords(['language' => 'ar']);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->language === 'ar');
    }

    /**
     * Test filtering by jurisdiction
     */
    public function test_get_filtered_consent_records_by_jurisdiction(): void
    {
        ConsentRecord::factory(3)->create(['jurisdiction' => 'eu']);
        ConsentRecord::factory(2)->create(['jurisdiction' => 'uae']);

        $result = $this->repository->getFilteredConsentRecords(['jurisdiction' => 'eu']);

        $this->assertCount(3, $result->items());
        $this->assertTrue($result->items()[0]->jurisdiction === 'eu');
    }

    /**
     * Test filtering by subject_realm
     */
    public function test_get_filtered_consent_records_by_subject_realm(): void
    {
        ConsentRecord::factory(4)->create(['subject_realm' => 'individual']);
        ConsentRecord::factory(2)->create(['subject_realm' => 'organization']);

        $result = $this->repository->getFilteredConsentRecords(['subject_realm' => 'individual']);

        $this->assertCount(4, $result->items());
        $this->assertTrue($result->items()[0]->subject_realm === 'individual');
    }

    /**
     * Test filtering with multiple criteria
     */
    public function test_get_filtered_consent_records_with_multiple_filters(): void
    {
        ConsentRecord::factory(5)->create([
            'status' => 'active',
            'language' => 'en',
            'jurisdiction' => 'eu',
        ]);
        ConsentRecord::factory(3)->create([
            'status' => 'draft',
            'language' => 'en',
            'jurisdiction' => 'eu',
        ]);

        $result = $this->repository->getFilteredConsentRecords([
            'status' => 'active',
            'language' => 'en',
            'jurisdiction' => 'eu',
        ]);

        $this->assertCount(5, $result->items());
    }

    /**
     * Test creating a consent record
     */
    public function test_create_consent_record(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = [
            'consent_code' => 'CNY-2025-test-001',
            'subject_key' => 'subject-123',
            'subject_realm' => 'individual',
            'subject_age_group' => '18-25',
            'purpose' => 'marketing',
            'record_of_processing_activity_id' => $ropa->id,
            'status' => 'active',
            'lifecycle_stage' => 'development',
            'consent_version' => 1,
            'consent_text' => 'I agree to the terms and conditions',
            'consent_method' => 'online',
            'effective_from' => now()->toDateString(),
            'source_system' => 'web_portal',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'language' => 'en',
            'jurisdiction' => 'eu',
            'data_categories' => ['name', 'email'],
            'can_withdraw' => true,
            'withdrawal_method' => 'email',
        ];

        $consentRecord = $this->repository->createConsentRecord($data);

        $this->assertInstanceOf(ConsentRecord::class, $consentRecord);
        $this->assertEquals('CNY-2025-test-001', $consentRecord->consent_code);
        $this->assertEquals('subject-123', $consentRecord->subject_key);
        $this->assertEquals('individual', $consentRecord->subject_realm);
        $this->assertDatabaseHas('consent_records', ['consent_code' => 'CNY-2025-test-001']);
    }

    /**
     * Test creating consent record with minimal data
     */
    public function test_create_consent_record_with_minimal_data(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = [
            'consent_code' => 'CNY-2025-minimal',
            'subject_key' => 'subject-456',
            'subject_realm' => 'organization',
            'purpose' => 'analytics',
            'record_of_processing_activity_id' => $ropa->id,
            'status' => 'draft',
            'lifecycle_stage' => 'design',
            'consent_version' => 1,
            'consent_text' => 'Minimal consent',
            'consent_method' => 'api',
            'effective_from' => now()->toDateString(),
            'source_system' => 'api',
            'ip_address' => '10.0.0.1',
            'user_agent' => 'API Client',
            'language' => 'en',
            'jurisdiction' => 'uae',
            'data_categories' => ['email'],
            'can_withdraw' => true,
            'withdrawal_method' => 'phone',
        ];

        $consentRecord = $this->repository->createConsentRecord($data);

        $this->assertNotNull($consentRecord->id);
        $this->assertEquals('CNY-2025-minimal', $consentRecord->consent_code);
    }

    /**
     * Test updating a consent record
     */
    public function test_update_consent_record(): void
    {
        $consentRecord = ConsentRecord::factory()->create([
            'status' => 'draft',
            'can_withdraw' => true,
        ]);

        $data = [
            'status' => 'active',
            'can_withdraw' => false,
        ];

        $updated = $this->repository->updateConsentRecord($consentRecord, $data);

        $this->assertEquals('active', $updated->status);
        $this->assertFalse($updated->can_withdraw);
        $this->assertDatabaseHas('consent_records', [
            'id' => $consentRecord->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test updating consent record with partial data
     */
    public function test_update_consent_record_with_partial_data(): void
    {
        $consentRecord = ConsentRecord::factory()->create([
            'status' => 'draft',
            'language' => 'en',
            'jurisdiction' => 'eu',
        ]);

        $data = [
            'status' => 'active',
        ];

        $updated = $this->repository->updateConsentRecord($consentRecord, $data);

        $this->assertEquals('active', $updated->status);
        $this->assertEquals('en', $updated->language);
        $this->assertEquals('eu', $updated->jurisdiction);
    }

    /**
     * Test updating consent record returns fresh instance
     */
    public function test_update_consent_record_returns_fresh_instance(): void
    {
        $consentRecord = ConsentRecord::factory()->create(['status' => 'draft']);

        $data = ['status' => 'active'];

        $updated = $this->repository->updateConsentRecord($consentRecord, $data);

        $this->assertEquals('active', $updated->status);
    }

    /**
     * Test deleting a consent record
     */
    public function test_delete_consent_record(): void
    {
        $consentRecord = ConsentRecord::factory()->create();
        $id = $consentRecord->id;

        $result = $this->repository->deleteConsentRecord($consentRecord);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('consent_records', ['id' => $id]);
    }

    /**
     * Test empty filters return all records
     */
    public function test_get_filtered_consent_records_with_empty_filters(): void
    {
        ConsentRecord::factory(5)->create();

        $result = $this->repository->getFilteredConsentRecords([]);

        $this->assertCount(5, $result->items());
    }

    /**
     * Test filtering with non-matching criteria returns empty
     */
    public function test_get_filtered_consent_records_with_non_matching_criteria(): void
    {
        ConsentRecord::factory(5)->create(['status' => 'active']);

        $result = $this->repository->getFilteredConsentRecords(['status' => 'archived']);

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test pagination with total count
     */
    public function test_get_filtered_consent_records_pagination_with_total(): void
    {
        ConsentRecord::factory(25)->create();

        $result = $this->repository->getFilteredConsentRecords(['per_page' => 10]);

        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
        $this->assertTrue($result->hasPages());
    }

    /**
     * Test creating record with related ROPA
     */
    public function test_create_consent_record_with_related_ropa(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = ConsentRecord::factory()->make([
            'record_of_processing_activity_id' => $ropa->id,
        ])->toArray();

        $consentRecord = $this->repository->createConsentRecord($data);

        $this->assertEquals($ropa->id, $consentRecord->record_of_processing_activity_id);
    }
}
