<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'users' => UserResource::collection($this->whenLoaded('users')),
            'frameworks' => FrameworkResource::collection($this->whenLoaded('frameworks')),
        ];
    }
}
