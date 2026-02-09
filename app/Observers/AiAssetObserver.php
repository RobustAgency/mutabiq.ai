<?php

namespace App\Observers;

use App\Models\AiAsset;

class AiAssetObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'vendor_id',
            'vendor_effective_from',
            'vendor_effective_to',
            'vendor_agreement_id',
        ];
    }

    public function created(AiAsset $aiAsset): void
    {
        $this->logCreate($aiAsset);
    }

    public function updating(AiAsset $aiAsset): void
    {
        $this->logUpdate($aiAsset, $aiAsset->getOriginal());
    }

    public function deleted(AiAsset $aiAsset): void
    {
        $this->logDelete($aiAsset);
    }
}
