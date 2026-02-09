<?php

namespace App\Observers;

use App\Models\AiRiskRegister;

class AiRiskRegisterObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'title',
            'risk_level',
            'status',
            'decision',
            'inherent_score',
            'residual_score',
        ];
    }

    public function created(AiRiskRegister $aiRiskRegister): void
    {
        $this->logCreate($aiRiskRegister);
    }

    public function updating(AiRiskRegister $aiRiskRegister): void
    {
        $this->logUpdate($aiRiskRegister, $aiRiskRegister->getOriginal());
    }

    public function deleted(AiRiskRegister $aiRiskRegister): void
    {
        $this->logDelete($aiRiskRegister);
    }
}
