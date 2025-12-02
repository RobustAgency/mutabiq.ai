<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\KriIndicator
 */
class KriIndicatorResource extends JsonResource
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
            'name' => $this->name,
            'definition' => $this->definition,
            'directionality' => $this->directionality,
            'unit' => $this->unit,
            'sample_window' => $this->sample_window,
            'threshold_warning' => $this->threshold_warning,
            'threshold_critical' => $this->threshold_critical,
            'data_source' => $this->data_source,
            'collection_method' => $this->collection_method,
            'frequency' => $this->frequency,
            'alert_routing' => $this->alert_routing,
            'action_on_breach' => $this->action_on_breach,
            'status' => $this->status,
            'owner_team' => $this->owner_team,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'ai_risk_register' => new AiRiskRegisterResource($this->whenLoaded('aiRiskRegister')),
        ];
    }
}
