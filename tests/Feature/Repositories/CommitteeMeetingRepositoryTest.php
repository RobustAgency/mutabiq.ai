<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\AiCommittee;
use App\Models\CommitteeMeeting;
use App\Enums\CommitteeMeeting\MeetingType;
use App\Enums\CommitteeMeeting\AttendancePolicy;
use App\Repositories\CommitteeMeetingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeMeetingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CommitteeMeetingRepository $repository;

    protected AiCommittee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CommitteeMeetingRepository;
        $this->committee = AiCommittee::factory()->create();
    }

    public function test_paginate_returns_paginated_results(): void
    {
        CommitteeMeeting::factory()->count(20)->create(['ai_committee_id' => $this->committee->id]);

        $result = $this->repository->getFilteredCommitteeMeetings(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_default_pagination_is_15(): void
    {
        CommitteeMeeting::factory()->count(20)->create(['ai_committee_id' => $this->committee->id]);

        $result = $this->repository->getFilteredCommitteeMeetings();

        $this->assertCount(15, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_filter_by_meeting_type(): void
    {
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::REGULAR->value,
        ]);
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::EMERGENCY->value,
        ]);

        $result = $this->repository->getFilteredCommitteeMeetings(['meeting_type' => MeetingType::REGULAR->value]);

        $this->assertCount(1, $result->items());
        $this->assertEquals(MeetingType::REGULAR->value, $result->items()[0]->meeting_type);
    }

    public function test_filter_by_attendance_policy(): void
    {
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
        ]);
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'attendance_policy' => AttendancePolicy::NO_QUORUM_REQUIRED->value,
        ]);

        $result = $this->repository->getFilteredCommitteeMeetings([
            'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
        ]);

        $this->assertCount(1, $result->items());
        $this->assertEquals(AttendancePolicy::QUORUM_REQUIRED->value, $result->items()[0]->attendance_policy);
    }

    public function test_filter_by_ai_committee_id(): void
    {
        $committee2 = AiCommittee::factory()->create();

        CommitteeMeeting::factory()->count(3)->create(['ai_committee_id' => $this->committee->id]);
        CommitteeMeeting::factory()->count(2)->create(['ai_committee_id' => $committee2->id]);

        $result = $this->repository->getFilteredCommitteeMeetings(['ai_committee_id' => $this->committee->id]);

        $this->assertCount(3, $result->items());
        $this->assertTrue($result->items()[0]->relationLoaded('committee'));
    }

    public function test_filter_by_scheduled_after(): void
    {
        now()->setTestNow('2026-01-15 12:00:00');

        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'scheduled_at' => now()->subDays(5),
        ]);
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'scheduled_at' => now()->addDays(5),
        ]);

        $result = $this->repository->getFilteredCommitteeMeetings([
            'scheduled_after' => now()->toDateString(),
        ]);

        $this->assertCount(1, $result->items());
    }

    public function test_filter_by_scheduled_before(): void
    {
        now()->setTestNow('2026-01-15 12:00:00');

        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'scheduled_at' => now()->subDays(5),
        ]);
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'scheduled_at' => now()->addDays(5),
        ]);

        $result = $this->repository->getFilteredCommitteeMeetings([
            'scheduled_before' => now()->toDateString(),
        ]);

        $this->assertCount(1, $result->items());
    }

    public function test_handles_all_meeting_types(): void
    {
        foreach (MeetingType::cases() as $type) {
            $meeting = $this->repository->createCommitteeMeeting([
                'ai_committee_id' => $this->committee->id,
                'meeting_type' => $type->value,
                'scheduled_at' => now()->addDays(1),
                'agenda' => 'Test agenda',
                'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
            ]);

            $this->assertEquals($type->value, $meeting->meeting_type);
        }
    }

    public function test_handles_all_attendance_policies(): void
    {
        foreach (AttendancePolicy::cases() as $policy) {
            $meeting = $this->repository->createCommitteeMeeting([
                'ai_committee_id' => $this->committee->id,
                'meeting_type' => MeetingType::REGULAR->value,
                'scheduled_at' => now()->addDays(1),
                'agenda' => 'Test agenda',
                'attendance_policy' => $policy->value,
            ]);

            $this->assertEquals($policy->value, $meeting->attendance_policy);
        }
    }

    public function test_create_committee_meeting(): void
    {
        $data = [
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::REGULAR->value,
            'scheduled_at' => now()->addDays(1),
            'duration_minutes' => 60,
            'agenda' => 'Quarterly review',
            'materials_link' => 'https://example.com/materials',
            'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
            'minutes_link' => null,
        ];

        $meeting = $this->repository->createCommitteeMeeting($data);

        $this->assertInstanceOf(CommitteeMeeting::class, $meeting);
        $this->assertEquals($data['meeting_type'], $meeting->meeting_type);
        $this->assertEquals($data['agenda'], $meeting->agenda);
        $this->assertDatabaseHas('committee_meetings', ['id' => $meeting->id]);
    }

    public function test_update_committee_meeting(): void
    {
        $meeting = CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'agenda' => 'Original agenda',
        ]);

        $data = ['agenda' => 'Updated agenda'];

        $updated = $this->repository->updateCommitteeMeeting($meeting, $data);

        $this->assertEquals('Updated agenda', $updated->agenda);
        $this->assertDatabaseHas('committee_meetings', ['id' => $meeting->id, 'agenda' => 'Updated agenda']);
        $this->assertTrue($updated->relationLoaded('committee'));
    }

    public function test_update_committee_meeting_partial(): void
    {
        $meeting = CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::REGULAR->value,
            'agenda' => 'Original agenda',
        ]);

        $data = ['meeting_type' => MeetingType::EMERGENCY->value];

        $updated = $this->repository->updateCommitteeMeeting($meeting, $data);

        $this->assertEquals(MeetingType::EMERGENCY->value, $updated->meeting_type);
        $this->assertEquals('Original agenda', $updated->agenda);
    }

    public function test_delete_committee_meeting(): void
    {
        $meeting = CommitteeMeeting::factory()->create(['ai_committee_id' => $this->committee->id]);
        $meetingId = $meeting->id;

        $result = $this->repository->deleteCommitteeMeeting($meeting);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('committee_meetings', ['id' => $meetingId]);
    }

    public function test_eager_loads_committee_relationship(): void
    {
        CommitteeMeeting::factory()->count(5)->create(['ai_committee_id' => $this->committee->id]);

        $result = $this->repository->getFilteredCommitteeMeetings();

        foreach ($result->items() as $meeting) {
            $this->assertTrue($meeting->relationLoaded('committee'));
        }
    }

    public function test_orders_by_scheduled_at_descending(): void
    {
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'scheduled_at' => now()->addDays(1),
        ]);
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'scheduled_at' => now()->addDays(3),
        ]);
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'scheduled_at' => now()->addDays(2),
        ]);

        $result = $this->repository->getFilteredCommitteeMeetings();
        $meetings = $result->items();

        $this->assertTrue($meetings[0]->scheduled_at > $meetings[1]->scheduled_at);
        $this->assertTrue($meetings[1]->scheduled_at > $meetings[2]->scheduled_at);
    }
}
