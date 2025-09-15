<?php

namespace App\Http\Resources;

use App\Models\Control;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Control
 */
class ControlResource extends JsonResource
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
            'code' => $this->code,
            'question' => $this->question,
            'summary' => $this->summary,
            'description' => $this->description,
            'frameworks' => FrameworkResource::collection($this->whenLoaded('frameworks')),
            'requirements' => RequirementResource::collection($this->whenLoaded('requirements')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
