<?php

namespace App\Observers;

use App\Models\IncidentRootCauseAnalysis;

class IncidentRootCauseAnalysisObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'rca_method',
            'analysis_date',
            'immediate_cause',
            'root_causes',
            'contributing_factors',
            'control_failures',
            'recommendations',
            'lead_analyst',
            'review_committee',
            'approved_at',
            'report_link',
        ];
    }

    public function created(IncidentRootCauseAnalysis $rca): void
    {
        $this->logCreate($rca);
    }

    public function updating(IncidentRootCauseAnalysis $rca): void
    {
        $this->logUpdate($rca, $rca->getOriginal());
    }

    public function deleted(IncidentRootCauseAnalysis $rca): void
    {
        $this->logDelete($rca);
    }
}
