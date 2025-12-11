<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\AiRiskRegister;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiRiskRegister
 */
class AiRiskRegisterResource extends JsonResource
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
            'organization_id' => $this->organization_id,
            'title' => $this->title,
            'risk_category' => $this->risk_category,
            'ai_model_id' => $this->ai_model_id,
            'ai_model' => $this->whenLoaded('aiModel'),
            'ai_model_version_id' => $this->ai_model_version_id,
            'ai_model_version' => $this->whenLoaded('aiModelVersion'),
            'use_case_id' => $this->use_case_id,
            'use_case' => $this->whenLoaded('useCase'),
            'description' => $this->description,
            'related_controls' => $this->related_controls,
            'likelihood_code' => $this->likelihood_code,
            'impact_code' => $this->impact_code,
            'inherent_score' => $this->inherent_score,
            'residual_score' => $this->residual_score,
            'risk_level' => $this->risk_level,
            'decision' => $this->decision,
            'risk_owner' => $this->risk_owner,
            'risk_owner_details' => $this->whenLoaded('riskOwner'),
            'review_cadence' => $this->review_cadence,
            'next_review_due' => $this->next_review_due->toDateString(),
            'status' => $this->status,
            'linked_assessment_id' => $this->linked_assessment_id,
            'linked_incident_id' => $this->linked_incident_id,
            'linked_capa_id' => $this->linked_capa_id,
            'evidence_link' => $this->evidence_link,
            'likelihood_label_snapshot' => $this->likelihood_label_snapshot,
            'impact_label_snapshot' => $this->impact_label_snapshot,
            'method_name_snapshot' => $this->method_name_snapshot,
            'aiRiskMethodology' => new RiskMethodologyResource($this->whenLoaded('aiRiskMethodology')),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
