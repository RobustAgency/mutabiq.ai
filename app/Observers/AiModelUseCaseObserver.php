<?php

namespace App\Observers;

use App\Models\AiModelUseCase;

class AiModelUseCaseObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'relationship_type',
            'ai_model_version_id',
        ];
    }

    public function created(AiModelUseCase $aiModelUseCase): void
    {
        $this->logCreate($aiModelUseCase);
    }

    public function updating(AiModelUseCase $aiModelUseCase): void
    {
        $this->logUpdate($aiModelUseCase, $aiModelUseCase->getOriginal());
    }

    public function deleted(AiModelUseCase $aiModelUseCase): void
    {
        $this->logDelete($aiModelUseCase);
    }
}
