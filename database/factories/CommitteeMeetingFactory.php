<?php

namespace Database\Factories;

use App\Models\AiCommittee;
use App\Models\CommitteeMeeting;
use App\Enums\CommitteeMeeting\MeetingType;
use App\Enums\CommitteeMeeting\AttendancePolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommitteeMeetingFactory extends Factory
{
    protected $model = CommitteeMeeting::class;

    public function definition(): array
    {
        return [
            'ai_committee_id' => AiCommittee::factory(),
            'meeting_type' => $this->faker->randomElement(MeetingType::cases())->value,
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+3 months'),
            'duration_minutes' => $this->faker->numberBetween(30, 180),
            'agenda' => $this->faker->paragraph(),
            'materials_link' => $this->faker->url(),
            'attendance_policy' => $this->faker->randomElement(AttendancePolicy::cases())->value,
            'attendance_roster' => json_encode([
                'attendees' => [
                    [
                        'name' => $this->faker->name(),
                        'role' => $this->faker->jobTitle(),
                        'status' => $this->faker->randomElement(['present', 'absent', 'excused']),
                    ],
                    [
                        'name' => $this->faker->name(),
                        'role' => $this->faker->jobTitle(),
                        'status' => $this->faker->randomElement(['present', 'absent', 'excused']),
                    ],
                ],
                'total_invited' => $this->faker->numberBetween(5, 15),
                'total_attended' => $this->faker->numberBetween(3, 12),
            ]),
            'minutes_link' => $this->faker->optional()->url(),
        ];
    }

    public function regular(): self
    {
        return $this->state(fn () => [
            'meeting_type' => MeetingType::REGULAR->value,
        ]);
    }

    public function adHoc(): self
    {
        return $this->state(fn () => [
            'meeting_type' => MeetingType::AD_HOC->value,
        ]);
    }

    public function emergency(): self
    {
        return $this->state(fn () => [
            'meeting_type' => MeetingType::EMERGENCY->value,
        ]);
    }

    public function quorumRequired(): self
    {
        return $this->state(fn () => [
            'attendance_policy' => AttendancePolicy::QUORUM_REQUIRED->value,
        ]);
    }

    public function noQuorumRequired(): self
    {
        return $this->state(fn () => [
            'attendance_policy' => AttendancePolicy::NO_QUORUM_REQUIRED->value,
        ]);
    }
}
