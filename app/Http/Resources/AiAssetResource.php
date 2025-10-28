<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AiAsset
 */
class AiAssetResource extends JsonResource
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
            'vendor_id' => $this->vendor_id,
            'vendor_effective_from' => $this->vendor_effective_from?->toIso8601String(),
            'vendor_effective_to' => $this->vendor_effective_to?->toIso8601String(),
            'vendor_agreement_id' => $this->vendor_agreement_id,
            'vendor_assessment_id' => $this->vendor_assessment_id,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'vendor_agreement' => new AgreementResource($this->whenLoaded('vendorAgreement')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
