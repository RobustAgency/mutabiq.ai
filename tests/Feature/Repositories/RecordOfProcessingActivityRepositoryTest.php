<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\RecordOfProcessingActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\RecordOfProcessingActivityRepository;

class RecordOfProcessingActivityRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private RecordOfProcessingActivityRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RecordOfProcessingActivityRepository;
    }

    /**
     * Test get filtered activities returns paginated results.
     */
    public function test_get_filtered_activities_returns_paginated_results(): void
    {
        RecordOfProcessingActivity::factory()->count(20)->create();

        $result = $this->repository->getFilteredActivities(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /**
     * Test get filtered activities uses default per page value.
     */
    public function test_get_filtered_activities_uses_default_per_page(): void
    {
        RecordOfProcessingActivity::factory()->count(20)->create();

        $result = $this->repository->getFilteredActivities();

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get filtered activities filters by status.
     */
    public function test_get_filtered_activities_filters_by_status(): void
    {
        RecordOfProcessingActivity::factory()->count(5)->create(['status' => 'draft']);
        RecordOfProcessingActivity::factory()->count(3)->create(['status' => 'active']);

        $result = $this->repository->getFilteredActivities(['status' => 'active']);

        $this->assertCount(3, $result->items());
        foreach ($result->items() as $item) {
            $this->assertEquals('active', $item->status);
        }
    }

    /**
     * Test get filtered activities filters by owner team.
     */
    public function test_get_filtered_activities_filters_by_owner_team(): void
    {
        RecordOfProcessingActivity::factory()->count(5)->create(['owner_team' => 'hr']);
        RecordOfProcessingActivity::factory()->count(3)->create(['owner_team' => 'finance']);

        $result = $this->repository->getFilteredActivities(['owner_team' => 'hr']);

        $this->assertCount(5, $result->items());
        foreach ($result->items() as $item) {
            $this->assertEquals('hr', $item->owner_team);
        }
    }

    /**
     * Test get filtered activities orders by created_at descending by default.
     */
    public function test_get_filtered_activities_orders_by_created_at_desc(): void
    {
        $old = RecordOfProcessingActivity::factory()->create(['created_at' => now()->subDays(10)]);
        $new = RecordOfProcessingActivity::factory()->create(['created_at' => now()]);

        $result = $this->repository->getFilteredActivities();

        $this->assertEquals($new->id, $result->items()[0]->id);
        $this->assertEquals($old->id, $result->items()[1]->id);
    }

    /**
     * Test get filtered activities returns empty when no records.
     */
    public function test_get_filtered_activities_returns_empty_when_no_records(): void
    {
        $result = $this->repository->getFilteredActivities();

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test create activity creates new record with all fields.
     */
    public function test_create_activity_creates_new_record_with_all_fields(): void
    {
        $user = User::factory()->create();
        $data = [
            'activity_code' => 'RPA-TEST-001',
            'activity_name' => 'Test Activity',
            'purpose' => 'Testing',
            'detailed_purpose' => 'Detailed testing purpose',
            'owner_team' => 'it',
            'controller_role' => 'controller',
            'data_subject_categories' => ['customers'],
            'data_categories' => ['contact'],
            'contains_pii' => true,
            'consent_required' => true,
            'lawful_basis' => 'consent',
            'legitimate_interest_assessment' => 'Assessment text',
            'consent_coverage_percent' => 100,
            'dpia_required' => true,
            'dpia_status' => 'in_progress',
            'dpia_id' => 'dpia-123',
            'retention_period' => '1 year',
            'retention_justification' => 'Legal requirement',
            'has_international_transfers' => false,
            'applicable_jurisdictions' => ['eu'],
            'security_measures' => 'Encryption and access control',
            'internal_recipients' => ['IT Department'],
            'external_recipients' => [],
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'version' => 1,
        ];

        $activity = $this->repository->createActivity($data);

        $this->assertInstanceOf(RecordOfProcessingActivity::class, $activity);
        $this->assertEquals($data['activity_code'], $activity->activity_code);
        $this->assertEquals($data['activity_name'], $activity->activity_name);
        $this->assertEquals($data['purpose'], $activity->purpose);
        $this->assertTrue($activity->contains_pii);
        $this->assertNotNull($activity->id);
    }

    /**
     * Test create activity with minimal required fields.
     */
    public function test_create_activity_with_minimal_fields(): void
    {
        $user = User::factory()->create();
        $data = [
            'activity_code' => 'RPA-MIN-001',
            'activity_name' => 'Minimal Activity',
            'purpose' => 'Minimal testing',
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
            'retention_period' => '2 years',
            'retention_justification' => 'Business requirement',
            'has_international_transfers' => false,
            'applicable_jurisdictions' => ['uk'],
            'security_measures' => 'Standard security',
            'internal_recipients' => [],
            'external_recipients' => [],
            'status' => 'draft',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'version' => 1,
        ];

        $activity = $this->repository->createActivity($data);

        $this->assertInstanceOf(RecordOfProcessingActivity::class, $activity);
        $this->assertNull($activity->detailed_purpose);
        $this->assertNull($activity->legitimate_interest_assessment);
        $this->assertNull($activity->dpia_id);
    }

    /**
     * Test update activity updates fields.
     */
    public function test_update_activity_updates_fields(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create([
            'activity_name' => 'Original Name',
            'status' => 'draft',
        ]);

        $updateData = [
            'activity_name' => 'Updated Name',
            'status' => 'active',
        ];

        $updated = $this->repository->updateActivity($activity, $updateData);

        $this->assertEquals('Updated Name', $updated->activity_name);
        $this->assertEquals('active', $updated->status);
    }

    /**
     * Test update activity returns fresh instance.
     */
    public function test_update_activity_returns_fresh_instance(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create(['activity_name' => 'Original']);

        $updated = $this->repository->updateActivity($activity, ['activity_name' => 'Updated']);

        $this->assertNotSame($activity, $updated);
        $this->assertEquals('Updated', $updated->activity_name);
    }

    /**
     * Test update activity persists to database.
     */
    public function test_update_activity_persists_to_database(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create(['purpose' => 'Original Purpose']);

        $this->repository->updateActivity($activity, ['purpose' => 'Updated Purpose']);

        $persisted = RecordOfProcessingActivity::find($activity->id);
        $this->assertEquals('Updated Purpose', $persisted->purpose);
    }

    /**
     * Test delete activity removes from database.
     */
    public function test_delete_activity_removes_from_database(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create();
        $id = $activity->id;

        $result = $this->repository->deleteActivity($activity);

        $this->assertTrue($result);
        $this->assertNull(RecordOfProcessingActivity::find($id));
    }

    /**
     * Test delete activity returns true.
     */
    public function test_delete_activity_returns_true(): void
    {
        $activity = RecordOfProcessingActivity::factory()->create();

        $result = $this->repository->deleteActivity($activity);

        $this->assertTrue($result);
    }

    /**
     * Test get filtered activities with multiple filters.
     */
    public function test_get_filtered_activities_with_multiple_filters(): void
    {
        RecordOfProcessingActivity::factory()->count(2)->create([
            'status' => 'active',
            'owner_team' => 'it',
            'contains_pii' => true,
        ]);
        RecordOfProcessingActivity::factory()->count(2)->create([
            'status' => 'active',
            'owner_team' => 'hr',
            'contains_pii' => true,
        ]);
        RecordOfProcessingActivity::factory()->count(3)->create([
            'status' => 'draft',
            'owner_team' => 'it',
            'contains_pii' => true,
        ]);

        $result = $this->repository->getFilteredActivities([
            'status' => 'active',
            'owner_team' => 'it',
            'contains_pii' => true,
        ]);

        $this->assertCount(2, $result->items());
    }
}
