<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\RiskMethodology
 */
class RiskMethodologyResource extends JsonResource
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
            'likelihood_scale' => $this->likelihood_scale,
            'impact_scale' => $this->impact_scale,
            'matrix_rule' => $this->matrix_rule,
            'acceptance_thresholds' => $this->acceptance_thresholds,
            'aggregation_logic' => $this->aggregation_logic,
            'review_policy' => $this->review_policy,
            'effective_from' => $this->effective_from,
            'effective_to' => $this->effective_to,
            'owner_team' => $this->owner_team,
            'source_created_at' => $this->source_created_at,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'created_at' => $this->created_at->toString(),
            'updated_at' => $this->updated_at->toString(),
        ];
    }
}
