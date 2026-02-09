<?php

namespace App\Observers;

use App\Models\AiModelCard;

class AiModelCardObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'title',
            'status',
            'publication_status',
            'publication_date',
            'last_review_date',
            'next_review_date',
        ];
    }

    public function created(AiModelCard $aiModelCard): void
    {
        $this->logCreate($aiModelCard);
    }

    public function updating(AiModelCard $aiModelCard): void
    {
        $this->logUpdate($aiModelCard, $aiModelCard->getOriginal());
    }

    public function deleted(AiModelCard $aiModelCard): void
    {
        $this->logDelete($aiModelCard);
    }
}
