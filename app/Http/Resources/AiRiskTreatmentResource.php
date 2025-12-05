<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\AiRiskTreatment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiRiskTreatment
 */
class AiRiskTreatmentResource extends JsonResource
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
            'ai_risk_register_id' => $this->ai_risk_register_id,
            'treatment_type' => $this->treatment_type,
            'plan_summary' => $this->plan_summary,
            'owner_stakeholder_id' => $this->owner_stakeholder_id,
            'assignee' => $this->assignee,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'expected_residual_level' => $this->expected_residual_level,
            'result_verification' => $this->result_verification,
            'evidence_link' => $this->evidence_link,
            'linked_capa_id' => $this->linked_capa_id,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at->toString(),
            'updated_at' => $this->updated_at->toString(),
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'aiRiskRegister' => new AiRiskRegisterResource($this->whenLoaded('aiRiskRegister')),
        ];
    }
}
