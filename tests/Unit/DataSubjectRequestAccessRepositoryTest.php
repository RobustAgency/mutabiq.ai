<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\DataSubjectRequestAccess;
use App\Enums\DataSubjectRequestAccess\Status;
use App\Enums\DataSubjectRequestAccess\Priority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\DataSubjectRequestAccess\RequestType;
use App\Enums\DataSubjectRequestAccess\SubjectRealm;
use App\Enums\DataSubjectRequestAccess\RequestSource;
use App\Repositories\DataSubjectRequestAccessRepository;
use App\Enums\DataSubjectRequestAccess\VerificationStatus;

class DataSubjectRequestAccessRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DataSubjectRequestAccessRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(DataSubjectRequestAccessRepository::class);
    }

    /**
     * Test getting filtered data subject request accesses with default pagination
     */
    public function test_get_filtered_with_default_pagination(): void
    {
        DataSubjectRequestAccess::factory(20)->create();

        $result = $this->repository->getFilteredDataSubjectRequestAccesses();

        $this->assertCount(15, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test getting filtered data subject request accesses with custom per_page
     */
    public function test_get_filtered_with_custom_per_page(): void
    {
        DataSubjectRequestAccess::factory(30)->create();

        $result = $this->repository->getFilteredDataSubjectRequestAccesses(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(30, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    /**
     * Test filtering by status
     */
    public function test_get_filtered_by_status(): void
    {
        DataSubjectRequestAccess::factory(5)->create(['status' => Status::NEW->value]);
        DataSubjectRequestAccess::factory(3)->create(['status' => Status::COMPLETED->value]);

        $result = $this->repository->getFilteredDataSubjectRequestAccesses(['status' => Status::COMPLETED->value]);

        $this->assertCount(3, $result->items());
        $this->assertTrue($result->items()[0]->status === Status::COMPLETED->value);
    }

    /**
     * Test filtering by request_type
     */
    public function test_get_filtered_by_request_type(): void
    {
        DataSubjectRequestAccess::factory(4)->create(['request_type' => RequestType::ACCESS->value]);
        DataSubjectRequestAccess::factory(2)->create(['request_type' => RequestType::ERASURE->value]);

        $result = $this->repository->getFilteredDataSubjectRequestAccesses(['request_type' => RequestType::ERASURE->value]);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->request_type === RequestType::ERASURE->value);
    }

    /**
     * Test filtering by verification_status
     */
    public function test_get_filtered_by_verification_status(): void
    {
        DataSubjectRequestAccess::factory(3)->create(['verification_status' => VerificationStatus::PENDING->value]);
        DataSubjectRequestAccess::factory(2)->create(['verification_status' => VerificationStatus::VERIFIED->value]);

        $result = $this->repository->getFilteredDataSubjectRequestAccesses(['verification_status' => VerificationStatus::VERIFIED->value]);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->verification_status === VerificationStatus::VERIFIED->value);
    }

    /**
     * Test filtering by jurisdiction
     */
    public function test_get_filtered_by_jurisdiction(): void
    {
        DataSubjectRequestAccess::factory(5)->create(['jurisdiction' => 'EU']);
        DataSubjectRequestAccess::factory(2)->create(['jurisdiction' => 'UAE']);

        $result = $this->repository->getFilteredDataSubjectRequestAccesses(['jurisdiction' => 'UAE']);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->jurisdiction === 'UAE');
    }

    /**
     * Test filtering by subject_realm
     */
    public function test_get_filtered_by_subject_realm(): void
    {
        DataSubjectRequestAccess::factory(4)->create(['subject_realm' => SubjectRealm::CUSTOMER->value]);
        DataSubjectRequestAccess::factory(2)->create(['subject_realm' => SubjectRealm::EMPLOYEE->value]);

        $result = $this->repository->getFilteredDataSubjectRequestAccesses(['subject_realm' => SubjectRealm::EMPLOYEE->value]);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->subject_realm === SubjectRealm::EMPLOYEE->value);
    }

    /**
     * Test filtering by priority
     */
    public function test_get_filtered_by_priority(): void
    {
        DataSubjectRequestAccess::factory(3)->create(['priority' => Priority::LOW->value]);
        DataSubjectRequestAccess::factory(2)->create(['priority' => Priority::HIGH->value]);

        $result = $this->repository->getFilteredDataSubjectRequestAccesses(['priority' => Priority::HIGH->value]);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->priority === Priority::HIGH->value);
    }

    /**
     * Test filtering with multiple criteria
     */
    public function test_get_filtered_with_multiple_filters(): void
    {
        DataSubjectRequestAccess::factory(5)->create([
            'status' => Status::COMPLETED->value,
            'request_type' => RequestType::ACCESS->value,
            'priority' => Priority::HIGH->value,
        ]);
        DataSubjectRequestAccess::factory(3)->create([
            'status' => Status::NEW->value,
            'request_type' => RequestType::ERASURE->value,
            'priority' => Priority::LOW->value,
        ]);

        $result = $this->repository->getFilteredDataSubjectRequestAccesses([
            'status' => Status::COMPLETED->value,
            'request_type' => RequestType::ACCESS->value,
            'priority' => Priority::HIGH->value,
        ]);

        $this->assertCount(5, $result->items());
    }

    /**
     * Test empty filters return all records
     */
    public function test_get_filtered_with_empty_filters(): void
    {
        DataSubjectRequestAccess::factory(5)->create();

        $result = $this->repository->getFilteredDataSubjectRequestAccesses([]);

        $this->assertCount(5, $result->items());
    }

    /**
     * Test filtering with non-matching criteria returns empty
     */
    public function test_get_filtered_with_non_matching_criteria(): void
    {
        DataSubjectRequestAccess::factory(5)->create(['status' => Status::NEW->value]);

        $result = $this->repository->getFilteredDataSubjectRequestAccesses(['status' => Status::COMPLETED->value]);

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test pagination with total count
     */
    public function test_get_filtered_pagination_with_total(): void
    {
        DataSubjectRequestAccess::factory(25)->create();

        $result = $this->repository->getFilteredDataSubjectRequestAccesses(['per_page' => 10]);

        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
        $this->assertTrue($result->hasPages());
    }

    /**
     * Test creating a data subject request access
     */
    public function test_create_data_subject_request_access(): void
    {
        $user = User::factory()->create();
        $data = [
            'request_code' => 'DSR-001',
            'request_type' => RequestType::ACCESS->value,
            'subject_identifier' => 'user@example.com',
            'subject_key' => 'subj_123',
            'subject_name' => 'John Doe',
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'verification_status' => VerificationStatus::VERIFIED->value,
            'verification_method' => 'email',
            'verified_by' => 1,
            'request_details' => 'Please provide all my personal data',
            'requested_data_categories' => ['email', 'phone'],
            'request_source' => RequestSource::EMAIL->value,
            'submitted_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => Status::NEW->value,
            'priority' => Priority::NORMAL->value,
            'is_overdue' => false,
            'assigned_to' => $user->id,
            'assigned_date' => now(),
            'jurisdiction' => 'EU',
            'systems_checked' => 'Dwerty',
            'records_found' => 15,
        ];

        $dsr = $this->repository->createDataSubjectRequestAccess($data);

        $this->assertInstanceOf(DataSubjectRequestAccess::class, $dsr);
        $this->assertEquals('DSR-001', $dsr->request_code);
        $this->assertEquals(RequestType::ACCESS->value, $dsr->request_type);
        $this->assertEquals('John Doe', $dsr->subject_name);
        $this->assertDatabaseHas('data_subject_request_accesses', ['request_code' => 'DSR-001']);
    }

    /**
     * Test creating data subject request access with minimal data
     */
    public function test_create_data_subject_request_access_with_minimal_data(): void
    {
        $user = User::factory()->create();

        $data = [
            'request_code' => 'DSR-minimal',
            'request_type' => RequestType::ACCESS->value,
            'subject_identifier' => 'minimal@example.com',
            'subject_realm' => SubjectRealm::CUSTOMER->value,
            'verification_status' => VerificationStatus::PENDING->value,
            'request_details' => 'Access request',
            'request_source' => RequestSource::WEB_FORM->value,
            'submitted_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => Status::NEW->value,
            'priority' => Priority::NORMAL->value,
            'is_overdue' => false,
            'assigned_to' => $user->id,
            'assigned_date' => now(),
            'jurisdiction' => 'EU',
            'systems_checked' => 'Dwerty',
        ];

        $dsr = $this->repository->createDataSubjectRequestAccess($data);

        $this->assertNotNull($dsr->id);
        $this->assertEquals('DSR-minimal', $dsr->request_code);
    }

    /**
     * Test updating a data subject request access
     */
    public function test_update_data_subject_request_access(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create([
            'status' => Status::NEW->value,
            'priority' => Priority::LOW->value,
        ]);

        $data = [
            'status' => Status::IN_PROGRESS->value,
            'priority' => Priority::HIGH->value,
        ];

        $updated = $this->repository->updateDataSubjectRequestAccess($dsr, $data);

        $this->assertEquals(Status::IN_PROGRESS->value, $updated->status);
        $this->assertEquals(Priority::HIGH->value, $updated->priority);
        $this->assertDatabaseHas('data_subject_request_accesses', [
            'id' => $dsr->id,
            'status' => Status::IN_PROGRESS->value,
        ]);
    }

    /**
     * Test updating data subject request access with partial data
     */
    public function test_update_data_subject_request_access_with_partial_data(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create([
            'status' => Status::NEW->value,
            'priority' => Priority::LOW->value,
            'request_type' => RequestType::ACCESS->value,
        ]);

        $data = ['status' => Status::IN_PROGRESS->value];

        $updated = $this->repository->updateDataSubjectRequestAccess($dsr, $data);

        $this->assertEquals(Status::IN_PROGRESS->value, $updated->status);
        $this->assertEquals(Priority::LOW->value, $updated->priority);
        $this->assertEquals(RequestType::ACCESS->value, $updated->request_type);
    }

    /**
     * Test updating data subject request access returns fresh instance
     */
    public function test_update_data_subject_request_access_returns_fresh_instance(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create(['status' => Status::NEW->value]);

        $data = ['status' => Status::IN_PROGRESS->value];

        $updated = $this->repository->updateDataSubjectRequestAccess($dsr, $data);

        $this->assertEquals(Status::IN_PROGRESS->value, $updated->status);
    }

    /**
     * Test deleting a data subject request access
     */
    public function test_delete_data_subject_request_access(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->create();
        $id = $dsr->id;

        $result = $this->repository->deleteDataSubjectRequestAccess($dsr);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('data_subject_request_accesses', ['id' => $id]);
    }

    /**
     * Test deleting non-existent data subject request access
     */
    public function test_delete_non_existent_data_subject_request_access(): void
    {
        $dsr = DataSubjectRequestAccess::factory()->make();

        $result = $this->repository->deleteDataSubjectRequestAccess($dsr);

        $this->assertFalse($result);
    }

    /**
     * Test filtering by status with different statuses
     */
    public function test_filtering_by_all_status_values(): void
    {
        foreach (Status::cases() as $status) {
            DataSubjectRequestAccess::factory()->create(['status' => $status->value]);
        }

        foreach (Status::cases() as $status) {
            $result = $this->repository->getFilteredDataSubjectRequestAccesses(['status' => $status->value]);
            $this->assertCount(1, $result->items());
            $this->assertEquals($status->value, $result->items()[0]->status);
        }
    }

    /**
     * Test filtering by priority with different priorities
     */
    public function test_filtering_by_all_priority_values(): void
    {
        foreach (Priority::cases() as $priority) {
            DataSubjectRequestAccess::factory()->create(['priority' => $priority->value]);
        }

        foreach (Priority::cases() as $priority) {
            $result = $this->repository->getFilteredDataSubjectRequestAccesses(['priority' => $priority->value]);
            $this->assertCount(1, $result->items());
            $this->assertEquals($priority->value, $result->items()[0]->priority);
        }
    }
}
