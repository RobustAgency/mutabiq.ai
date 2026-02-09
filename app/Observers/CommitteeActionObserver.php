<?php

namespace App\Observers;

use App\Models\CommitteeAction;

class CommitteeActionObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'title',
            'action_type',
            'assignee_id',
            'due_date',
            'status',
            'verification_result',
            'evidence_link',
            'notes',
            'closed_at',
        ];
    }

    public function created(CommitteeAction $committeeAction): void
    {
        $this->logCreate($committeeAction);
    }

    public function updating(CommitteeAction $committeeAction): void
    {
        $this->logUpdate($committeeAction, $committeeAction->getOriginal());
    }

    public function deleted(CommitteeAction $committeeAction): void
    {
        $this->logDelete($committeeAction);
    }
}
