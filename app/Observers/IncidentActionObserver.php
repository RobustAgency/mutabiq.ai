<?php

namespace App\Observers;

use App\Models\IncidentAction;

class IncidentActionObserver extends ActivityAwareObserver
{
    /**
     * Define which fields to track for changes.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [
            'action_type',
            'execution_status',
            'individual_name',
            'approval_required',
            'estimated_duration',
            'actual_duration',
            'description',
            'performed_by',
            'started_at',
            'completed_at',
            'validation_result',
            'validation_notes',
        ];
    }

    /**
     * Handle the IncidentAction "created" event.
     */
    public function created(IncidentAction $action): void
    {
        $this->logCreate($action);
    }

    /**
     * Handle the IncidentAction "updating" event.
     */
    public function updating(IncidentAction $action): void
    {
        $this->logUpdate($action, $action->getOriginal());
    }

    /**
     * Handle the IncidentAction "deleted" event.
     */
    public function deleted(IncidentAction $action): void
    {
        $this->logDelete($action);
    }
}
