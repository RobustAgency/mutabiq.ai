<?php

namespace App\Http\Resources;

use App\Models\UseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UseCase
 */
class UseCaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'problem_statement' => $this->problem_statement,
            'expected_business_value' => $this->expected_business_value,
            'status' => $this->status,
            'business_domain' => $this->business_domain,
            'roi_classification' => $this->roi_classification,
            'priority' => $this->priority,
            'data_sensitivity' => $this->data_sensitivity,
            'expected_roi' => $this->expected_roi,
            'estimated_time_savings' => $this->estimated_time_savings,
            'estimated_cost_savings' => $this->estimated_cost_savings,
            'estimated_revenue_impact' => $this->estimated_revenue_impact,
            'success_metrics' => $this->success_metrics,
            'preliminary_risk_level' => $this->preliminary_risk_level,
            'regulatory_impact' => $this->regulatory_impact,
            'potential_harm' => $this->potential_harm,
            'human_oversight_mode' => $this->human_oversight_mode,
            'dependencies' => $this->dependencies,
            'budget_allocated' => $this->budget_allocated,
            'target_deployment_date' => $this->target_deployment_date,
            'estimated_fte_saving' => $this->estimated_fte_saving,
            'data_availability_status' => $this->data_availability_status,
            'data_readiness' => $this->data_readiness,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'business_owner_id' => $this->business_owner_id,
            'technical_owner_id' => $this->technical_owner_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'business_owner' => $this->whenLoaded('businessOwner'),
            'technical_owner' => $this->whenLoaded('technicalOwner'),
        ];
    }
}
