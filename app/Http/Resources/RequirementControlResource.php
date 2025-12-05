<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\RequirementControl;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RequirementControl
 */
class RequirementControlResource extends JsonResource
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
            'requirement_id' => $this->requirement_id,
            'control_id' => $this->control_id,
            'ai_model_id' => $this->ai_model_id,
            'coverage' => $this->coverage,
            'interpretation_notes' => $this->interpretation_notes,
            'residual_gaps' => $this->residual_gaps,
            'review_status' => $this->review_status,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'requirement' => new RequirementResource($this->whenLoaded('requirement')),
            'control' => new ControlResource($this->whenLoaded('control')),
            'ai_model' => new AiModelResource($this->whenLoaded('aiModel')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
