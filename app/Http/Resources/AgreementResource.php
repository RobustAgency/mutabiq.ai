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
            'display_id' => $this->display_id,
            'organization_id' => $this->organization_id,
            'vendor_id' => $this->vendor_id,
            'agreement_type' => $this->agreement_type,
            'status' => $this->status,
            'agreement_owner_id' => $this->agreement_owner_id,
            'asset_types_covered' => $this->asset_types_covered,
            'renewal_type' => $this->renewal_type,
            'notice_period_days' => $this->notice_period_days,
            'termination_for_convenience' => $this->termination_for_convenience,
            'governing_law' => $this->governing_law,
            'effective_from' => $this->effective_from->toIso8601String(),
            'effective_to' => $this->effective_to->toIso8601String(),
            'training_opt_out' => $this->training_opt_out,
            'audit_rights' => $this->audit_rights,
            'transfer_mechanism' => $this->transfer_mechanism,
            'sub_processing_rights' => $this->sub_processing_rights,
            'contract_value' => $this->contract_value,
            'liability_cap' => $this->liability_cap,
            'insurance_requirements' => $this->insurance_requirements,
            'indemnification' => $this->indemnification,
            'internal_reference_number' => $this->internal_reference_number,
            'vendor_contract_id' => $this->vendor_contract_id,
            'dispute_resolution' => $this->dispute_resolution,
            'confidentiality_term' => $this->confidentiality_term,
            'parent_agreement' => $this->parent_agreement,
            'replaces_agreement' => $this->replaces_agreement,
            'notes' => $this->notes,
            'sla_terms' => $this->sla_terms,
            'doc_ref' => $this->doc_ref,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
        ];
    }
}
