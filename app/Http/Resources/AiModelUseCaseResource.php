<?php

namespace App\Http\Resources;

use App\Models\AiModelUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiModelUseCase
 */
class AiModelUseCaseResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'business_objective' => $this->business_objective,
            'status' => $this->status,
            'business_domain' => $this->business_domain,
            'business_owner_email' => $this->business_owner_email,
            'technical_owner_email' => $this->technical_owner_email,
            'regulatory_scope' => $this->regulatory_scope,
            'data_sensitivity' => $this->data_sensitivity,
            'go_live_date' => $this->go_live_date,
            'expected_roi' => $this->expected_roi,
            'implementation_cost' => $this->implementation_cost,
            'reduction_in_time' => $this->reduction_in_time,
            'reduction_in_cost' => $this->reduction_in_cost,
            'increase_in_revenue' => $this->increase_in_revenue,
            'risk_avoidance' => $this->risk_avoidance,
            'fte_capacity_saved' => $this->fte_capacity_saved,
            'use_case_type' => $this->use_case_type,
            'value_driver' => $this->value_driver,
            'risk_level' => $this->risk_level,
            'overall_risk_score' => $this->overall_risk_score,
            'human_oversight_mode' => $this->human_oversight_mode,
            'dpia' => $this->dpia,
            'aia' => $this->aia,
            'data_availability_status' => $this->data_availability_status,
            'data_readiness_level' => $this->data_readiness_level,
            'data_freshness' => $this->data_freshness,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
