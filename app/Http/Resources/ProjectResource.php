<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
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
            'description' => $this->description,
            'governance_pillar' => $this->governance_pillar,
            'progress' => $this->progress,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'total_requirements' => $this->total_requirements,
            'total_controls' => $this->total_controls,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'frameworks' => FrameworkResource::collection($this->whenLoaded('frameworks')),
        ];
    }
}
