<?php

namespace App\Http\Resources;

use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Requirement
 */
class RequirementResource extends JsonResource
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
            'reference' => $this->reference,
            'requirement_text' => $this->requirement_text,
            'category' => $this->category,
            'applicability' => $this->applicability,
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_to' => $this->effective_to?->toDateString(),
            'supersedes_req_id' => $this->supersedes_req_id,
            'superseded_by_req_id' => $this->superseded_by_req_id,
            'priority' => $this->priority,
            'tags' => $this->tags,
            'frameworks' => FrameworkResource::collection($this->whenLoaded('frameworks')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
