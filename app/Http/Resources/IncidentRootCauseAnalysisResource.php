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
            'ai_incident_id' => $this->ai_incident_id,
            'rca_method' => $this->rca_method,
            'immediate_cause' => $this->immediate_cause,
            'latent_causes' => $this->latent_causes,
            'contributing_factors' => $this->contributing_factors,
            'impact_assessment' => $this->impact_assessment,
            'fixes_implemented' => $this->fixes_implemented,
            'lessons_learned' => $this->lessons_learned,
            'recommendations' => $this->recommendations,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at ? Carbon::parse($this->approved_at)->toIso8601String() : null,
            'report_link' => $this->report_link,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'ai_incident' => new AiIncidentResource($this->whenLoaded('aiIncident')),
        ];
    }
}
