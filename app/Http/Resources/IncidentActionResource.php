<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\IncidentAction
 */
class IncidentActionResource extends JsonResource
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
            'action_type' => $this->action_type,
            'execution_status' => $this->execution_status,
            'description' => $this->description,
            'performed_by' => $this->performed_by,
            'individual_name' => $this->individual_name,
            'depends_on' => $this->depends_on,
            'approval_required' => $this->approval_required,
            'estimated_duration' => $this->estimated_duration,
            'actual_duration' => $this->actual_duration,
            'started_at' => $this->started_at ? Carbon::parse($this->started_at)->toIso8601String() : null,
            'completed_at' => $this->completed_at ? Carbon::parse($this->completed_at)->toIso8601String() : null,
            'validation_result' => $this->validation_result,
            'validation_notes' => $this->validation_notes,
            'linked_release_id' => $this->linked_release_id,
            'evidence_link' => $this->evidence_link,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'ai_incident' => new AiIncidentResource($this->whenLoaded('aiIncident')),
        ];
    }
}
