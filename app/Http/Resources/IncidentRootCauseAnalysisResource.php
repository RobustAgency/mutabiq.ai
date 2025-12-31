<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\IncidentRootCauseAnalysis
 */
class IncidentRootCauseAnalysisResource extends JsonResource
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
            'ai_incident_id' => $this->ai_incident_id,
            'rca_method' => $this->rca_method,
            'analysis_date' => $this->analysis_date ? Carbon::parse($this->analysis_date)->toIso8601String() : null,
            'immediate_cause' => $this->immediate_cause,
            'root_causes' => $this->root_causes,
            'contributing_factors' => $this->contributing_factors,
            'control_failures' => $this->control_failures,
            'recommendations' => $this->recommendations,
            'lead_analyst' => $this->lead_analyst,
            'review_committee' => $this->review_committee,
            'approved_at' => $this->approved_at ? Carbon::parse($this->approved_at)->toIso8601String() : null,
            'report_link' => $this->report_link,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'ai_incident' => new AiIncidentResource($this->whenLoaded('aiIncident')),
        ];
    }
}
