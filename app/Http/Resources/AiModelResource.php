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
            'organization_id' => $this->organization_id,
            'category' => $this->category,
            'type' => $this->type,
            'technical_domain' => $this->technical_domain,
            'purpose' => $this->purpose,
            'criticality_level' => $this->criticality_level,
            'business_adoption_status' => $this->business_adoption_status,
            'regulatory_risk_tier' => $this->regulatory_risk_tier,
            'eu_ai_category' => $this->eu_ai_category,
            'ownership_category' => $this->ownership_category,
            'responsible_org_role' => $this->responsible_org_role,
            'business_owner_id' => $this->business_owner_id,
            'custodian_id' => $this->custodian_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
