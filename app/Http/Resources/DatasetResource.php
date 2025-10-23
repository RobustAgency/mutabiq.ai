<?php

namespace App\Http\Resources;

use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Dataset
 */
class DatasetResource extends JsonResource
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
            'source_ids' => $this->source_ids,
            'purpose' => $this->purpose,
            'schema_summary' => $this->schema_summary,
            'sensitivity' => $this->sensitivity,
            'contains_pii' => $this->contains_pii,
            'data_subject_categories' => $this->data_subject_categories,
            'controller_role' => $this->controller_role,
            'lawful_basis' => $this->lawful_basis,
            'lawful_basis_detail' => $this->lawful_basis_detail,
            'consent_required' => $this->consent_required,
            'consent_coverage_pct' => $this->consent_coverage_pct,
            'consent_source_ref' => $this->consent_source_ref,
            'licensing_basis' => $this->licensing_basis,
            'license_type' => $this->license_type,
            'privacy_notice_ref' => $this->privacy_notice_ref,
            'cross_border_transfer' => $this->cross_border_transfer,
            'data_structure' => $this->data_structure,
            'storage_format' => $this->storage_format,
            'content_types' => $this->content_types,
            'retention_policy_ref' => $this->retention_policy_ref,
            'dpia_ref' => $this->dpia_ref,
            'aia_ref' => $this->aia_ref,
            'owner_team' => $this->owner_team,
            'refresh_cadence' => $this->refresh_cadence,
            'quality_SLA' => $this->quality_SLA,
            'catalog_asset_id' => $this->catalog_asset_id,
            'catalog_uri' => $this->catalog_uri,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
