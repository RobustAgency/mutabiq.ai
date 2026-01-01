<?php

namespace App\Http\Resources;

use App\Models\AiIncident;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiIncident
 */
class AiIncidentResource extends JsonResource
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
            'display_id' => $this->display_id,
            'organization_id' => $this->organization_id,
            'title' => $this->title,
            'summary' => $this->summary,
            'incident_type' => $this->incident_type,
            'domain' => $this->domain,
            'severity' => $this->severity,
            'status' => $this->status,
            'incident_commander' => $this->incident_commander,
            'response_team' => $this->response_team,
            'primary_regulatory_framework' => $this->primary_regulatory_framework,
            'notification_requirement' => $this->notification_requirement,
            'data_residency_affected' => $this->data_residency_affected,
            'regulatory_reference' => $this->regulatory_reference,
            'estimated_impacted_users' => $this->estimated_impacted_users,
            'estimated_impacted_records' => $this->estimated_impacted_records,
            'data_types_impacted' => $this->data_types_impacted,
            'affected_business_units' => $this->affected_business_units,
            'external_parties_involved' => $this->external_parties_involved,
            'business_impact_description' => $this->business_impact_description,
            'impacted_systems' => $this->impacted_systems,
            'ai_model_id' => $this->ai_model_id,
            'linked_dataset_id' => $this->linked_dataset_id,
            'linked_risk_id' => $this->linked_risk_id,
            'linked_assessment_id' => $this->linked_assessment_id,
            'evidence_link' => $this->evidence_link,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
