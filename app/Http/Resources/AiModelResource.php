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
            'organization_id' => $this->organization_id,
            'source_org_stakeholder_id' => $this->source_org_stakeholder_id,
            'owner_stakeholder_id' => $this->owner_stakeholder_id,
            'vendor_id' => $this->vendor_id,
            'current_version_id' => $this->current_version_id,
            'primary_category' => $this->primary_category,
            'type' => $this->type,
            'domain_specialization' => $this->domain_specialization,
            'operational_status' => $this->operational_status,
            'business_status' => $this->business_status,
            'regulatory_risk_classification' => $this->regulatory_risk_classification,
            'ownership_type' => $this->ownership_type,
            'development_source' => $this->development_source,
            'created_by' => $this->whenLoaded('createdBy', fn() => [
                'id' => $this->createdBy?->id,
                'name' => $this->createdBy?->name,
            ]),
            'updated_by' => $this->whenLoaded('updatedBy', fn() => [
                'id' => $this->updatedBy?->id,
                'name' => $this->updatedBy?->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
