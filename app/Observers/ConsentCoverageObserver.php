<?php

namespace App\Observers;

use App\Models\ConsentCoverage;

class ConsentCoverageObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'dataset_id',
            'snapshot_id',
            'purpose',
            'jurisdiction',
            'as_of',
            'subjects_total',
            'subjects_with_valid_consent',
            'coverage_pct',
            'evidence_ref',
            'source_created_at',
        ];
    }

    public function created(ConsentCoverage $consentCoverage): void
    {
        $this->logCreate($consentCoverage);
    }

    public function updating(ConsentCoverage $consentCoverage): void
    {
        $this->logUpdate($consentCoverage, $consentCoverage->getOriginal());
    }

    public function deleted(ConsentCoverage $consentCoverage): void
    {
        $this->logDelete($consentCoverage);
    }
}
