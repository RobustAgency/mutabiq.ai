<?php

namespace App\Observers;

use App\Models\AiRiskTreatment;

class AiRiskTreatmentObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'treatment_type',
            'status',
            'due_date',
            'expected_residual_level',
            'closed_at',
        ];
    }

    public function created(AiRiskTreatment $aiRiskTreatment): void
    {
        $this->logCreate($aiRiskTreatment);
    }

    public function updating(AiRiskTreatment $aiRiskTreatment): void
    {
        $this->logUpdate($aiRiskTreatment, $aiRiskTreatment->getOriginal());
    }

    public function deleted(AiRiskTreatment $aiRiskTreatment): void
    {
        $this->logDelete($aiRiskTreatment);
    }
}
