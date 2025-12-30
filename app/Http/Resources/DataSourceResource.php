<?php

namespace App\Http\Resources;

use App\Models\DataSource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DataSource
 */
class DataSourceResource extends JsonResource
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
            'display_id' => $this->display_id,
            'name' => $this->name,
            'description' => $this->description,
            'system_type' => $this->system_type,
            'owner_team' => $this->owner_team,
            'data_domains' => $this->data_domains,
            'residency' => $this->residency,
            'criticality_level' => $this->criticality_level,
            'hosting_model' => $this->hosting_model,
            'technical_owner' => $this->technical_owner,
            'business_owner' => $this->business_owner,
            'last_review_date' => $this->last_review_date,
            'next_review_date' => $this->next_review_date,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
