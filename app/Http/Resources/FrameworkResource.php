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
            'code' => $this->code,
            'type' => $this->type,
            'geography' => $this->geography,
            'category' => $this->category,
            'version' => $this->version,
            'release_date' => $this->release_date?->toDateString(),
            'is_published' => $this->is_published,
            'description' => $this->description,
            'authority_publisher' => $this->authority_publisher,
            'binding_level' => $this->binding_level,
            'sector_applicability' => $this->sector_applicability,
            'risk_class_coverage' => $this->risk_class_coverage,
            'certification_attestation' => $this->certification_attestation,
            'assessment_mode' => $this->assessment_mode,
            'framework_logo' => $this->getFirstMediaUrl('framework_logos') ?: null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'requirements' => RequirementResource::collection($this->whenLoaded('requirements')),
            'controls' => ControlResource::collection($this->whenLoaded('controls')),

        ];
    }
}
