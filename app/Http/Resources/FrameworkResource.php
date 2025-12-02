<?php

namespace App\Http\Resources;

use App\Models\Framework;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Framework
 */
class FrameworkResource extends JsonResource
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
            'version' => $this->version,
            'jurisdictions' => $this->jurisdictions,
            'scope' => $this->scope,
            'status' => $this->status,
            'effective_date' => $this->effective_date->toDateString(),
            'source_url' => $this->source_url,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'requirements' => RequirementResource::collection($this->whenLoaded('requirements')),
            'controls' => ControlResource::collection($this->whenLoaded('controls')),

        ];
    }
}
