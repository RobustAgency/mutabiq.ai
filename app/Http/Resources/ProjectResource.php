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
        $framework = $this->framework;

        $totalRequirements = 0;
        $totalControls = 0;

        if ($framework) {
            $totalRequirements = $framework->requirements_count
                ?? ($framework->relationLoaded('requirements') ? $framework->requirements->count() : 0);
            $totalControls = $framework->controls_count
                ?? ($framework->relationLoaded('controls') ? $framework->controls->count() : 0);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'governance_pillar' => $this->governance_pillar,
            'progress' => $this->progress,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'total_requirements' => $totalRequirements,
            'total_controls' => $totalControls,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'framework' => $framework ? new FrameworkResource($this->whenLoaded('framework')) : null,
        ];
    }
}
