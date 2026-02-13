<?php

namespace App\Observers;

use App\Models\RiskMethodology;

class RiskMethodologyObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'name',
            'likelihood_scale',
            'impact_scale',
            'matrix_rule',
            'acceptance_thresholds',
            'aggregation_logic',
            'review_policy',
            'effective_from',
            'effective_to',
            'owner_team',
            'source_created_at',
        ];
    }

    public function created(RiskMethodology $methodology): void
    {
        $this->logCreate($methodology);
    }

    public function updating(RiskMethodology $methodology): void
    {
        $this->logUpdate($methodology, $methodology->getOriginal());
    }

    public function deleted(RiskMethodology $methodology): void
    {
        $this->logDelete($methodology);
    }
}
