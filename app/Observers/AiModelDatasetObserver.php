<?php

namespace App\Observers;

use App\Models\AiModelDataset;

class AiModelDatasetObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'role',
            'rows_used',
            'training_start_date',
            'training_end_date',
            'bias_mitigation_applied',
            'linkage_status',
        ];
    }

    public function created(AiModelDataset $aiModelDataset): void
    {
        $this->logCreate($aiModelDataset);
    }

    public function updating(AiModelDataset $aiModelDataset): void
    {
        $this->logUpdate($aiModelDataset, $aiModelDataset->getOriginal());
    }

    public function deleted(AiModelDataset $aiModelDataset): void
    {
        $this->logDelete($aiModelDataset);
    }
}
