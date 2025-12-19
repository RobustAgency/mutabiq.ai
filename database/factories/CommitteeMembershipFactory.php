<?php

namespace Database\Factories;

use App\Models\AiCommittee;
use App\Models\Stakeholder;
use App\Enums\CommitteeMembership\MemberRole;
use App\Enums\CommitteeMembership\Eligibility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommitteeMembership>
 */
class CommitteeMembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ai_committee_id' => AiCommittee::factory(),
            'stakeholder_id' => Stakeholder::factory(),
            'eligibility' => $this->faker->randomElement(Eligibility::cases())->value,
            'member_role' => $this->faker->randomElement(MemberRole::cases())->value,
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->optional(0.5)->dateTimeBetween('now', '+1 year'),
            'expertise_tags' => $this->faker->randomElements(['governance', 'ethics', 'risk', 'compliance', 'technical'], rand(1, 3)),
        ];
    }
}
