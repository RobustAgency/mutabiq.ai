<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ComplianceEvidence
 */
class ComplianceEvidenceResource extends JsonResource
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
            'control_id' => $this->control_id,
            'requirement_id' => $this->requirement_id,
            'ai_model_id' => $this->ai_model_id,
            'artifact_type' => $this->artifact_type,
            'artifact_uri' => $this->artifact_uri,
            'sample_ids' => $this->sample_ids,
            'sampling_method' => $this->sampling_method,
            'collection_period_start' => $this->collection_period_start?->toDateTimeString(),
            'collection_period_end' => $this->collection_period_end?->toDateTimeString(),
            'collected_by' => $this->collected_by,
            'review_outcome' => $this->review_outcome,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'control' => new ControlResource($this->whenLoaded('control')),
            'requirement' => new RequirementResource($this->whenLoaded('requirement')),
            'ai_model' => new AiModelResource($this->whenLoaded('aiModel')),
            'collected_by_user' => new UserResource($this->whenLoaded('collectedBy')),
            'reviewed_by_user' => new UserResource($this->whenLoaded('reviewedBy')),
        ];
    }
}
