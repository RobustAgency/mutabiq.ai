<?php

namespace App\Observers;

use App\Models\CommitteeDecision;

class CommitteeDecisionObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'decision_type',
            'decision_scope',
            'ai_model_id',
            'use_case_id',
            'control_id',
            'rationale',
            'conditions',
            'expiry_date',
            'vote_method',
            'vote_result',
            'owner_team',
        ];
    }

    public function created(CommitteeDecision $committeeDecision): void
    {
        $this->logCreate($committeeDecision);
    }

    public function updating(CommitteeDecision $committeeDecision): void
    {
        $this->logUpdate($committeeDecision, $committeeDecision->getOriginal());
    }

    public function deleted(CommitteeDecision $committeeDecision): void
    {
        $this->logDelete($committeeDecision);
    }
}
