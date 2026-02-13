<?php

namespace App\Observers;

use App\Models\IncidentAlert;

class IncidentAlertObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'source_type',
            'data_source_id',
            'source_ref',
            'alert_sensitivity',
            'context',
            'first_seen_at',
            'last_seen_at',
            'auto_promote_incident',
            'evidence_link',
        ];
    }

    public function created(IncidentAlert $alert): void
    {
        $this->logCreate($alert);
    }

    public function updating(IncidentAlert $alert): void
    {
        $this->logUpdate($alert, $alert->getOriginal());
    }

    public function deleted(IncidentAlert $alert): void
    {
        $this->logDelete($alert);
    }
}
