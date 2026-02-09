<?php

namespace App\Observers;

use App\Models\AiModelVersion;

class AiModelVersionObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'version_number',
            'approval_status',
            'deployment_status',
            'lifecycle_stage',
            'release_date',
        ];
    }

    public function created(AiModelVersion $aiModelVersion): void
    {
        $this->logCreate($aiModelVersion);
    }

    public function updating(AiModelVersion $aiModelVersion): void
    {
        $this->logUpdate($aiModelVersion, $aiModelVersion->getOriginal());
    }

    public function deleted(AiModelVersion $aiModelVersion): void
    {
        $this->logDelete($aiModelVersion);
    }
}
