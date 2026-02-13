<?php

namespace App\Observers;

use App\Models\UseCase;

class UseCaseObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'name',
            'description',
            'problem_statement',
            'expected_business_value',
            'status',
            'business_domain',
            'roi_classification',
            'priority',
            'data_sensitivity',
            'expected_roi',
            'estimated_time_savings',
            'estimated_cost_savings',
            'estimated_revenue_impact',
            'success_metrics',
            'preliminary_risk_level',
            'regulatory_impact',
            'potential_harm',
            'human_oversight_mode',
            'dependencies',
            'budget_allocated',
            'target_deployment_date',
            'estimated_fte_saving',
            'data_availability_status',
            'data_readiness',
            'created_by',
            'updated_by',
            'business_owner_id',
            'technical_owner_id',
        ];
    }

    public function created(UseCase $useCase): void
    {
        $this->logCreate($useCase);
    }

    public function updating(UseCase $useCase): void
    {
        $this->logUpdate($useCase, $useCase->getOriginal());
    }

    public function deleted(UseCase $useCase): void
    {
        $this->logDelete($useCase);
    }
}
