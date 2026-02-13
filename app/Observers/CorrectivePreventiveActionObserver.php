<?php

namespace App\Observers;

use App\Models\CorrectivePreventiveAction;

class CorrectivePreventiveActionObserver extends ActivityAwareObserver
{
    /**
     * Define which fields to track for changes.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [
            'title',
            'capa_type',
            'priority',
            'root_cause',
            'actions',
            'owner_team',
            'assignee',
            'due_date',
            'status',
            'success_criteria',
            'effectiveness_review_date',
            'verification_result',
        ];
    }

    /**
     * Handle the CorrectivePreventiveAction "created" event.
     */
    public function created(CorrectivePreventiveAction $action): void
    {
        $this->logCreate($action);
    }

    /**
     * Handle the CorrectivePreventiveAction "updating" event.
     */
    public function updating(CorrectivePreventiveAction $action): void
    {
        $this->logUpdate($action, $action->getOriginal());
    }

    /**
     * Handle the CorrectivePreventiveAction "deleted" event.
     */
    public function deleted(CorrectivePreventiveAction $action): void
    {
        $this->logDelete($action);
    }
}
