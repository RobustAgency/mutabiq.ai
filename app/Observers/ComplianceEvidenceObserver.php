<?php

namespace App\Observers;

use App\Models\ComplianceEvidence;
use Illuminate\Database\Eloquent\Model;

class ComplianceEvidenceObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'artifact_type',
            'artifact_uri',
            'sample_ids',
            'sampling_method',
            'collection_period_start',
            'collection_period_end',
            'collected_by',
            'review_outcome',
            'reviewed_by',
            'reviewed_at',
            'hash_checksum',
        ];
    }

    /**
     * Override to get organization_id from the project relationship
     */
    protected function getModelOrganizationId(Model $model): ?int
    {
        return $model->project?->organization_id;
    }

    public function created(ComplianceEvidence $complianceEvidence): void
    {
        $this->logCreate($complianceEvidence);
    }

    public function updating(ComplianceEvidence $complianceEvidence): void
    {
        $this->logUpdate($complianceEvidence, $complianceEvidence->getOriginal());
    }

    public function deleted(ComplianceEvidence $complianceEvidence): void
    {
        $this->logDelete($complianceEvidence);
    }
}
