<?php

namespace Database\Factories;

use App\Models\Stakeholder;
use App\Models\CommitteeAction;
use App\Models\CommitteeDecision;
use App\Enums\CommitteeAction\Status;
use App\Enums\CommitteeAction\ActionType;
use App\Enums\CommitteeAction\VerificationResult;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommitteeActionFactory extends Factory
{
    protected $model = CommitteeAction::class;

    public function definition(): array
    {
        return [
            'committee_decision_id' => CommitteeDecision::factory(),
            'title' => $this->faker->sentence(),
            'action_type' => $this->faker->randomElement(ActionType::cases())->value,
            'assignee_id' => Stakeholder::factory(),
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            'status' => $this->faker->randomElement(Status::cases())->value,
            'verification_result' => $this->faker->randomElement(VerificationResult::cases())->value,
            'evidence_link' => $this->faker->optional()->url(),
            'notes' => $this->faker->optional()->paragraph(),
            'closed_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function statusNew(): self
    {
        return $this->state(fn () => [
            'status' => Status::NEW->value,
            'verification_result' => VerificationResult::PENDING->value,
            'closed_at' => null,
        ]);
    }

    public function inProgress(): self
    {
        return $this->state(fn () => [
            'status' => Status::IN_PROGRESS->value,
        ]);
    }

    public function blocked(): self
    {
        return $this->state(fn () => [
            'status' => Status::BLOCKED->value,
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn () => [
            'status' => Status::COMPLETED->value,
            'verification_result' => VerificationResult::PASSED->value,
            'closed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn () => [
            'status' => Status::CANCELLED->value,
            'closed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function implementChange(): self
    {
        return $this->state(fn () => [
            'action_type' => ActionType::IMPLEMENT_CHANGE->value,
        ]);
    }

    public function collectEvidence(): self
    {
        return $this->state(fn () => [
            'action_type' => ActionType::COLLECT_EVIDENCE->value,
        ]);
    }

    public function updatePolicy(): self
    {
        return $this->state(fn () => [
            'action_type' => ActionType::UPDATE_POLICY->value,
        ]);
    }

    public function conductAssessment(): self
    {
        return $this->state(fn () => [
            'action_type' => ActionType::CONDUCT_ASSESSMENT->value,
        ]);
    }

    public function notifyRegulator(): self
    {
        return $this->state(fn () => [
            'action_type' => ActionType::NOTIFY_REGULATOR->value,
        ]);
    }

    public function forDecision(CommitteeDecision $decision): self
    {
        return $this->state(fn () => [
            'committee_decision_id' => $decision->id,
        ]);
    }

    public function withAssignee(Stakeholder $stakeholder): self
    {
        return $this->state(fn () => [
            'assignee_id' => $stakeholder->id,
        ]);
    }
}
