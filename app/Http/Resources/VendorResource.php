<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Vendor
 */
class VendorResource extends JsonResource
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
            'vendor_name' => $this->vendor_name,
            'legal_name' => $this->legal_name,
            'hq_country' => $this->hq_country,
            'risk_tier' => $this->risk_tier,
            'status' => $this->status,
            'stakeholder_id' => $this->stakeholder_id,
            'primary_contacts' => $this->primary_contacts,
            'metadata' => $this->metadata,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
