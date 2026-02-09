<?php

namespace App\Observers;

use App\Models\AiIncident;

class AiIncidentObserver extends ActivityAwareObserver
{
    /**
     * Define which fields to track for changes.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [
            'status',
            'severity',
            'incident_commander',
            'notification_requirement',
            'title',
        ];
    }

    /**
     * Handle the AiIncident "created" event.
     */
    public function created(AiIncident $aiIncident): void
    {
        $this->logCreate($aiIncident);
    }

    /**
     * Handle the AiIncident "updating" event.
     */
    public function updating(AiIncident $aiIncident): void
    {
        $this->logUpdate($aiIncident, $aiIncident->getOriginal());
    }

    /**
     * Handle the AiIncident "deleted" event.
     */
    public function deleted(AiIncident $aiIncident): void
    {
        $this->logDelete($aiIncident);
    }
}
