<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiCommittee;
use App\Models\CommitteeMeeting;
use App\Enums\CommitteeMeeting\MeetingType;
use App\Enums\CommitteeMeeting\AttendancePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommitteeMeetingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected AiCommittee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->committee = AiCommittee::factory()->create();
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::REGULAR->value,
            'scheduled_at' => now()->addDays(1)->toDateString(),
            'duration_minutes' => 60,
            'agenda' => 'Quarterly review meeting',
            'materials_link' => 'https://example.com/materials',
            'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
            'minutes_link' => null,
        ], $overrides);
    }

    public function test_index_returns_paginated_committee_meetings(): void
    {
        CommitteeMeeting::factory()->count(20)->create(['ai_committee_id' => $this->committee->id]);

        $response = $this->actingAs($this->user)->getJson('/api/committee-meetings');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'ai_committee_id',
                        'meeting_type',
                        'scheduled_at',
                        'duration_minutes',
                        'agenda',
                        'materials_link',
                        'attendance_policy',
                        'attendance_roster',
                        'minutes_link',
                        'created_at',
                        'updated_at',
                        'committee' => [
                            'id',
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertCount(15, $response->json('data.data'));
        $this->assertEquals(20, $response->json('data.total'));
    }

    public function test_index_with_custom_per_page(): void
    {
        CommitteeMeeting::factory()->count(20)->create(['ai_committee_id' => $this->committee->id]);

        $response = $this->actingAs($this->user)->getJson('/api/committee-meetings?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_index_filter_by_meeting_type(): void
    {
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::REGULAR->value,
        ]);
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::EMERGENCY->value,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-meetings?meeting_type='.MeetingType::REGULAR->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals(MeetingType::REGULAR->value, $response->json('data.data.0.meeting_type'));
    }

    public function test_index_filter_by_attendance_policy(): void
    {
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
        ]);
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'attendance_policy' => AttendancePolicy::NO_QUORUM_REQUIRED->value,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-meetings?attendance_policy='.AttendancePolicy::QUORUM_REQUIRED->value);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals(AttendancePolicy::QUORUM_REQUIRED->value, $response->json('data.data.0.attendance_policy'));
    }

    public function test_index_filter_by_committee_id(): void
    {
        $committee2 = AiCommittee::factory()->create();

        CommitteeMeeting::factory()->count(3)->create(['ai_committee_id' => $this->committee->id]);
        CommitteeMeeting::factory()->count(2)->create(['ai_committee_id' => $committee2->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/committee-meetings?ai_committee_id='.$this->committee->id);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_store_creates_committee_meeting(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/committee-meetings', $payload);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'ai_committee_id',
                'meeting_type',
                'scheduled_at',
                'duration_minutes',
                'agenda',
                'materials_link',
                'attendance_policy',
                'attendance_roster',
                'minutes_link',
                'created_at',
                'updated_at',
            ],
        ]);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Committee meeting created successfully', $response->json('message'));
        $this->assertDatabaseHas('committee_meetings', [
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::REGULAR->value,
            'agenda' => 'Quarterly review meeting',
        ]);
    }

    public function test_store_with_regular_meeting_type(): void
    {
        $payload = $this->validPayload(['meeting_type' => MeetingType::REGULAR->value]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-meetings', $payload);

        $response->assertStatus(201);
        $this->assertEquals(MeetingType::REGULAR->value, $response->json('data.meeting_type'));
    }

    public function test_store_with_emergency_meeting_type(): void
    {
        $payload = $this->validPayload(['meeting_type' => MeetingType::EMERGENCY->value]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-meetings', $payload);

        $response->assertStatus(201);
        $this->assertEquals(MeetingType::EMERGENCY->value, $response->json('data.meeting_type'));
    }

    public function test_store_with_ad_hoc_meeting_type(): void
    {
        $payload = $this->validPayload(['meeting_type' => MeetingType::AD_HOC->value]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-meetings', $payload);

        $response->assertStatus(201);
        $this->assertEquals(MeetingType::AD_HOC->value, $response->json('data.meeting_type'));
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/committee-meetings', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ai_committee_id', 'meeting_type', 'scheduled_at', 'agenda', 'attendance_policy']);
    }

    public function test_store_validates_invalid_committee_id(): void
    {
        $payload = $this->validPayload(['ai_committee_id' => 9999]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-meetings', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ai_committee_id']);
    }

    public function test_store_validates_invalid_meeting_type(): void
    {
        $payload = $this->validPayload(['meeting_type' => 'invalid_type']);

        $response = $this->actingAs($this->user)->postJson('/api/committee-meetings', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['meeting_type']);
    }

    public function test_store_validates_invalid_attendance_policy(): void
    {
        $payload = $this->validPayload(['attendance_policy' => 'invalid_policy']);

        $response = $this->actingAs($this->user)->postJson('/api/committee-meetings', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['attendance_policy']);
    }

    public function test_store_with_optional_fields(): void
    {
        $payload = $this->validPayload([
            'duration_minutes' => null,
            'materials_link' => null,
            'minutes_link' => null,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/committee-meetings', $payload);

        $response->assertStatus(201);
        $this->assertNull($response->json('data.duration_minutes'));
        $this->assertNull($response->json('data.materials_link'));
        $this->assertNull($response->json('data.minutes_link'));
    }

    public function test_show_returns_committee_meeting(): void
    {
        $meeting = CommitteeMeeting::factory()->create(['ai_committee_id' => $this->committee->id]);

        $response = $this->actingAs($this->user)->getJson("/api/committee-meetings/{$meeting->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'ai_committee_id',
                'meeting_type',
                'scheduled_at',
                'duration_minutes',
                'agenda',
                'materials_link',
                'attendance_policy',
                'attendance_roster',
                'minutes_link',
                'created_at',
                'updated_at',
                'committee',
            ],
        ]);
        $this->assertEquals($meeting->id, $response->json('data.id'));
        $this->assertFalse($response->json('error'));
    }

    public function test_show_with_nonexistent_meeting(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/committee-meetings/9999');

        $response->assertStatus(404);
    }

    public function test_update_committee_meeting(): void
    {
        $meeting = CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'agenda' => 'Original agenda',
        ]);

        $payload = ['agenda' => 'Updated agenda'];

        $response = $this->actingAs($this->user)->postJson("/api/committee-meetings/{$meeting->id}", $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'agenda',
            ],
        ]);
        $this->assertEquals('Updated agenda', $response->json('data.agenda'));
        $this->assertDatabaseHas('committee_meetings', ['id' => $meeting->id, 'agenda' => 'Updated agenda']);
    }

    public function test_update_meeting_type(): void
    {
        $meeting = CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::REGULAR->value,
        ]);

        $payload = ['meeting_type' => MeetingType::EMERGENCY->value];

        $response = $this->actingAs($this->user)->postJson("/api/committee-meetings/{$meeting->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals(MeetingType::EMERGENCY->value, $response->json('data.meeting_type'));
    }

    public function test_update_attendance_policy(): void
    {
        $meeting = CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
        ]);

        $payload = ['attendance_policy' => AttendancePolicy::NO_QUORUM_REQUIRED->value];

        $response = $this->actingAs($this->user)->postJson("/api/committee-meetings/{$meeting->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals(AttendancePolicy::NO_QUORUM_REQUIRED->value, $response->json('data.attendance_policy'));
    }

    public function test_update_validates_invalid_meeting_type(): void
    {
        $meeting = CommitteeMeeting::factory()->create(['ai_committee_id' => $this->committee->id]);

        $payload = ['meeting_type' => 'invalid_type'];

        $response = $this->actingAs($this->user)->postJson("/api/committee-meetings/{$meeting->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['meeting_type']);
    }

    public function test_update_validates_invalid_committee_id(): void
    {
        $meeting = CommitteeMeeting::factory()->create(['ai_committee_id' => $this->committee->id]);

        $payload = ['ai_committee_id' => 9999];

        $response = $this->actingAs($this->user)->postJson("/api/committee-meetings/{$meeting->id}", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ai_committee_id']);
    }

    public function test_update_partial_update(): void
    {
        $meeting = CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::REGULAR->value,
            'agenda' => 'Original agenda',
        ]);

        $payload = ['meeting_type' => MeetingType::EMERGENCY->value];

        $response = $this->actingAs($this->user)->postJson("/api/committee-meetings/{$meeting->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals(MeetingType::EMERGENCY->value, $response->json('data.meeting_type'));
        $this->assertEquals('Original agenda', $response->json('data.agenda'));
    }

    public function test_delete_committee_meeting(): void
    {
        $meeting = CommitteeMeeting::factory()->create(['ai_committee_id' => $this->committee->id]);
        $meetingId = $meeting->id;

        $response = $this->actingAs($this->user)->deleteJson("/api/committee-meetings/{$meeting->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
        ]);
        $this->assertFalse($response->json('error'));
        $this->assertEquals('Committee meeting deleted successfully', $response->json('message'));
        $this->assertDatabaseMissing('committee_meetings', ['id' => $meetingId]);
    }

    public function test_delete_nonexistent_meeting(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/committee-meetings/9999');

        $response->assertStatus(404);
    }

    public function test_index_with_multiple_filters(): void
    {
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::REGULAR->value,
            'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
        ]);
        CommitteeMeeting::factory()->create([
            'ai_committee_id' => $this->committee->id,
            'meeting_type' => MeetingType::EMERGENCY->value,
            'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
        ]);

        $response = $this->actingAs($this->user)->getJson(
            '/api/committee-meetings?meeting_type='.MeetingType::REGULAR->value.
            '&attendance_policy='.AttendancePolicy::QUORUM_REQUIRED->value
        );

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals(MeetingType::REGULAR->value, $response->json('data.data.0.meeting_type'));
    }
}
