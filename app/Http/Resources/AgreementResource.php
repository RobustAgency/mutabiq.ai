<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Agreement
 */
class AgreementResource extends JsonResource
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
            'agreement_type' => $this->agreement_type,
            'status' => $this->status,
            'effective_from' => $this->effective_from->toIso8601String(),
            'effective_to' => $this->effective_to->toIso8601String(),
            'training_opt_out' => $this->training_opt_out,
            'audit_rights' => $this->audit_rights,
            'transfer_mechanism' => $this->transfer_mechanism,
            'sla_terms' => $this->sla_terms,
            'doc_ref' => $this->doc_ref,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
        ];
    }
}
