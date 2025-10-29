<?php

namespace App\Http\Resources;

use App\Models\AiIncident;
use Carbon\Carbon;
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
            'title' => $this->title,
            'summary' => $this->summary,
            'category' => $this->category,
            'severity' => $this->severity,
            'status' => $this->status,
            'stage' => $this->stage,
            'ic_owner' => $this->ic_owner,
            'ai_model_id' => $this->ai_model_id,
            'ai_model_version_id' => $this->ai_model_version_id,
            'use_case_id' => $this->use_case_id,
            'first_seen_at' => $this->first_seen_at ? Carbon::parse($this->first_seen_at)->toIso8601String() : null,
            'declared_at' => $this->declared_at ? Carbon::parse($this->declared_at)->toIso8601String() : null,
            'resolved_at' => $this->resolved_at ? Carbon::parse($this->resolved_at)->toIso8601String() : null,
            'closed_at' => $this->closed_at ? Carbon::parse($this->closed_at)->toIso8601String() : null,
            'impacted_users' => $this->impacted_users,
            'impacted_data' => $this->impacted_data,
            'impacted_systems' => $this->impacted_systems,
            'linked_release_id' => $this->linked_release_id,
            'linked_risk_id' => $this->linked_risk_id,
            'linked_assessment_id' => $this->linked_assessment_id,
            'linked_capa_id' => $this->linked_capa_id,
            'evidence_link' => $this->evidence_link,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
