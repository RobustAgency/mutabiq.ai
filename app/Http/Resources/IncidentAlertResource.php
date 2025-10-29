<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\IncidentAlert
 */
class IncidentAlertResource extends JsonResource
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
            'source_type' => $this->source_type,
            'source_ref' => $this->source_ref,
            'rule_version' => $this->rule_version,
            'context' => $this->context,
            'first_seen_at' => $this->first_seen_at ? Carbon::parse($this->first_seen_at)->toIso8601String() : null,
            'last_seen_at' => $this->last_seen_at ? Carbon::parse($this->last_seen_at)->toIso8601String() : null,
            'evidence_link' => $this->evidence_link,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'ai_incident' => new AiIncidentResource($this->whenLoaded('aiIncident')),
        ];
    }
}
