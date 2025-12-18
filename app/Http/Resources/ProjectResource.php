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
        $frameworkCounts = $this->getFrameworkCounts();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'governance_pillar' => $this->governance_pillar,
            'progress' => $this->progress,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'total_requirements' => $frameworkCounts['requirements'],
            'users' => ProjectMemberResource::collection($this->whenLoaded('users')),
            'framework' => $this->framework ? new FrameworkResource($this->whenLoaded('framework')) : null,
        ];
    }

    /**
     * Get the framework requirements and controls counts.
     *
     * @return array<string, int>
     */
    private function getFrameworkCounts(): array
    {
        $framework = $this->framework;

        if (! $framework) {
            return [
                'requirements' => 0,
                'controls' => 0,
            ];
        }

        $requirements = $framework->requirements_count
            ?? ($framework->relationLoaded('requirements') ? $framework->requirements->count() : 0);

        return [
            'requirements' => $requirements,
        ];
    }
}
