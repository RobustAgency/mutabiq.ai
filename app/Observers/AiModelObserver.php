<?php

namespace App\Observers;

use App\Models\AiModel;

class AiModelObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'name',
            'category',
            'type',
            'criticality_level',
            'regulatory_risk_tier',
            'business_adoption_status',
            'current_version_id',
        ];
    }

    public function created(AiModel $aiModel): void
    {
        $this->logCreate($aiModel);
    }

    public function updating(AiModel $aiModel): void
    {
        $this->logUpdate($aiModel, $aiModel->getOriginal());
    }

    public function deleted(AiModel $aiModel): void
    {
        $this->logDelete($aiModel);
    }
}
