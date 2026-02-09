<?php

namespace App\Observers;

use App\Models\ArtifactAccessLog;
use Illuminate\Database\Eloquent\Model;

class ArtifactAccessLogObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'action',
            'context',
            'reason',
        ];
    }

    /**
     * Override to get organization_id from the artifact relationship
     */
    protected function getModelOrganizationId(Model $model): ?int
    {
        return $model->artifact?->organization_id;
    }

    public function created(ArtifactAccessLog $artifactAccessLog): void
    {
        $this->logCreate($artifactAccessLog);
    }

    public function updating(ArtifactAccessLog $artifactAccessLog): void
    {
        $this->logUpdate($artifactAccessLog, $artifactAccessLog->getOriginal());
    }

    public function deleted(ArtifactAccessLog $artifactAccessLog): void
    {
        $this->logDelete($artifactAccessLog);
    }
}
