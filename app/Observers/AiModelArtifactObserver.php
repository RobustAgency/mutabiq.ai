<?php

namespace App\Observers;

use App\Models\AiModelArtifact;

class AiModelArtifactObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'name',
            'artifact_type',
            'uri',
            'file_format',
            'environment',
            'checksum_value',
        ];
    }

    public function created(AiModelArtifact $aiModelArtifact): void
    {
        $this->logCreate($aiModelArtifact);
    }

    public function updating(AiModelArtifact $aiModelArtifact): void
    {
        $this->logUpdate($aiModelArtifact, $aiModelArtifact->getOriginal());
    }

    public function deleted(AiModelArtifact $aiModelArtifact): void
    {
        $this->logDelete($aiModelArtifact);
    }
}
