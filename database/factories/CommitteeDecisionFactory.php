<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\Control;
use App\Models\UseCase;
use App\Models\CommitteeMeeting;
use App\Models\CommitteeDecision;
use App\Enums\CommitteeDecision\VoteMethod;
use App\Enums\CommitteeDecision\VoteResult;
use App\Enums\CommitteeDecision\DecisionType;
use App\Enums\CommitteeDecision\DecisionScope;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommitteeDecisionFactory extends Factory
{
    protected $model = CommitteeDecision::class;

    public function definition(): array
    {
        $decisionScope = $this->faker->randomElement(DecisionScope::cases())->value;

        return [
            'committee_meeting_id' => CommitteeMeeting::factory(),
            'decision_type' => $this->faker->randomElement(DecisionType::cases())->value,
            'decision_scope' => $decisionScope,
            'ai_model_id' => $decisionScope === DecisionScope::MODEL->value ? AiModel::factory() : null,
            'use_case_id' => $decisionScope === DecisionScope::USE_CASE->value ? UseCase::factory() : null,
            'control_id' => $decisionScope === DecisionScope::CONTROL->value ? Control::factory() : null,
            'related_ref' => $this->faker->optional()->slug(2),
            'rationale' => $this->faker->paragraph(),
            'conditions' => $this->faker->optional()->sentence(),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+1 month', '+1 year'),
            'vote_method' => $this->faker->randomElement(VoteMethod::cases())->value,
            'vote_result' => $this->faker->randomElement(VoteResult::cases())->value,
            'owner_team' => $this->faker->randomElement(['ai_governance', 'ethics_board', 'compliance_team', 'risk_management']),
        ];
    }

    public function forModel(AiModel $aiModel): self
    {
        return $this->state(fn () => [
            'decision_scope' => DecisionScope::MODEL->value,
            'ai_model_id' => $aiModel->id,
            'use_case_id' => null,
            'control_id' => null,
        ]);
    }

    public function forUseCase(UseCase $useCase): self
    {
        return $this->state(fn () => [
            'decision_scope' => DecisionScope::USE_CASE->value,
            'use_case_id' => $useCase->id,
            'ai_model_id' => null,
            'control_id' => null,
        ]);
    }

    public function forControl(Control $control): self
    {
        return $this->state(fn () => [
            'decision_scope' => DecisionScope::CONTROL->value,
            'control_id' => $control->id,
            'ai_model_id' => null,
            'use_case_id' => null,
        ]);
    }

    public function approved(): self
    {
        return $this->state(fn () => [
            'decision_type' => DecisionType::APPROVE->value,
            'vote_result' => VoteResult::PASSED->value,
        ]);
    }

    public function denied(): self
    {
        return $this->state(fn () => [
            'decision_type' => DecisionType::DENY->value,
            'vote_result' => VoteResult::FAILED->value,
        ]);
    }

    public function waived(): self
    {
        return $this->state(fn () => [
            'decision_type' => DecisionType::WAIVE->value,
            'vote_result' => VoteResult::NOT_APPLICABLE->value,
        ]);
    }
}
