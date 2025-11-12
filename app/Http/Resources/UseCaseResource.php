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
            'business_objective' => $this->business_objective,
            'business_owner_id' => $this->business_owner_id,
            'technical_owner_id' => $this->technical_owner_id,
            'business_domain' => $this->business_domain,
            'roi_classification' => $this->roi_classification,
            'priority' => $this->priority,
            'risk_level' => $this->risk_level,
            'data_sensitivity' => $this->data_sensitivity,
            'expected_roi_percentage' => $this->expected_roi_percentage,
            'budget_allocated' => $this->budget_allocated,
            'target_go_live_date' => $this->target_go_live_date?->format('Y-m-d'),
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'roi_assessment' => $this->roi_assessment,
            'risk_assessment' => $this->risk_assessment,
            'data_assessment' => $this->data_assessment,
            'estimated_implementation_cost' => $this->estimated_implementation_cost,
            'estimated_reduction_in_time' => $this->estimated_reduction_in_time,
            'estimated_reduction_in_cost' => $this->estimated_reduction_in_cost,
            'estimated_revenue_increase' => $this->estimated_revenue_increase,
            'estimated_fte_capacity_saving' => $this->estimated_fte_capacity_saving,
            'data_availability_status' => $this->data_availability_status,
            'data_readiness' => $this->data_readiness,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'business_owner' => $this->whenLoaded('businessOwner'),
            'technical_owner' => $this->whenLoaded('technicalOwner'),
        ];
    }
}
