<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PdpProcessingRegister
 */
class PdpProcessingRegisterResource extends JsonResource
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
            'purpose' => $this->purpose,
            'controller_role' => $this->controller_role,
            'data_subject_categories' => $this->data_subject_categories,
            'personal_data_categories' => $this->personal_data_categories,
            'lawful_basis' => $this->lawful_basis,
            'lawful_basis_detail' => $this->lawful_basis_detail,
            'retention_policy_ref' => $this->retention_policy_ref,
            'recipients' => $this->recipients,
            'international_transfer_ref' => $this->international_transfer_ref,
            'dpia_required_flag' => $this->dpia_required_flag,
            'security_measures_ref' => $this->security_measures_ref,
            'owner_team' => $this->owner_team,
            'effective_from' => $this->effective_from?->toIso8601String(),
            'effective_to' => $this->effective_to?->toIso8601String(),
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
