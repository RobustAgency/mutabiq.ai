<?php

namespace App\Http\Resources;

use App\Models\AiModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiModel
 */
class AiModelResource extends JsonResource
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
            'primary_category' => $this->primary_category,
            'type' => $this->type,
            'domain_specialization' => $this->domain_specialization,
            'operational_status' => $this->operational_status,
            'business_status' => $this->business_status,
            'total_versions' => $this->total_versions,
            'strategic_importance' => $this->strategic_importance,
            'regulatory_risk_classification' => $this->regulatory_risk_classification,
            'organizational_role' => $this->organizational_role,
            'ownership_type' => $this->ownership_type,
            'source_organization' => $this->source_organization,
            'current_owner' => $this->current_owner,
            'development_source' => $this->development_source,
            'created_by' => $this->whenLoaded('createdBy', fn() => $this->createdBy?->name),
            'updated_by' => $this->whenLoaded('updatedBy', fn() => $this->updatedBy?->name),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
