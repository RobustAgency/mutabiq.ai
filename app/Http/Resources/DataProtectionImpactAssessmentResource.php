<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DataProtectionImpactAssessment
 */
class DataProtectionImpactAssessmentResource extends JsonResource
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
            'dpia_code' => $this->dpia_code,
            'dpia_name' => $this->dpia_name,
            'ropa_id' => $this->ropa_id,
            'linked_ai_model_id' => $this->linked_ai_model_id,
            'linked_asset_type' => $this->linked_asset_type,
            'automated_trigger' => $this->automated_trigger,
            'trigger_reason' => $this->trigger_reason,
            'risk_level' => $this->risk_level,
            'risk_score' => $this->risk_score,
            'stage' => $this->stage,
            'completion_percentage' => $this->completion_percentage,
            'necessity_justification' => $this->necessity_justification,
            'proportionality_assessment' => $this->proportionality_assessment,
            'alternatives_considered' => $this->alternatives_considered,
            'identified_risks' => $this->identified_risks,
            'likelihood_assessment' => $this->likelihood_assessment,
            'impact_assessment' => $this->impact_assessment,
            'mitigation_measures' => $this->mitigation_measures,
            'residual_risk_level' => $this->residual_risk_level,
            'dpo_consulted' => $this->dpo_consulted,
            'dpo_consultation_date' => $this->dpo_consultation_date?->toIso8601String(),
            'dpo_advice' => $this->dpo_advice,
            'dpo_user_id' => $this->dpo_user_id,
            'stakeholders_consulted' => $this->stakeholders_consulted,
            'stakeholder_feedback' => $this->stakeholder_feedback,
            'data_subjects_consulted' => $this->data_subjects_consulted,
            'consultation_method' => $this->consultation_method,
            'final_decision' => $this->final_decision,
            'approval_date' => $this->approval_date?->toIso8601String(),
            'approved_by' => $this->approved_by,
            'conditions' => $this->conditions,
            'status' => $this->status,
            'review_frequency_months' => $this->review_frequency_months,
            'next_review_date' => $this->next_review_date?->toIso8601String(),
            'applicable_jurisdictions' => $this->applicable_jurisdictions,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
