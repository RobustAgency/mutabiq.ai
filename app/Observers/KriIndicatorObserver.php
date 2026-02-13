<?php

namespace App\Observers;

use App\Models\KriIndicator;

class KriIndicatorObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'name',
            'definition',
            'directionality',
            'unit',
            'sample_window',
            'threshold_warning',
            'threshold_critical',
            'data_source',
            'collection_method',
            'frequency',
            'alert_routing',
            'action_on_breach',
            'status',
            'owner_team',
            'notes',
            'created_by',
        ];
    }

    public function created(KriIndicator $indicator): void
    {
        $this->logCreate($indicator);
    }

    public function updating(KriIndicator $indicator): void
    {
        $this->logUpdate($indicator, $indicator->getOriginal());
    }

    public function deleted(KriIndicator $indicator): void
    {
        $this->logDelete($indicator);
    }
}
