<?php

namespace App\Http\Resources;

use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Dataset
 *
 * @property mixed $pivot
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
            'display_id' => $this->display_id,
            'name' => $this->name,
            'description' => $this->description,
            'source_ids' => $this->source_ids,
            'purpose' => $this->purpose,
            'owner_team' => $this->owner_team,
            'data_steward' => $this->data_steward,
            'status' => $this->status,
            'estimated_row_count' => $this->estimated_row_count,
            'estimated_size' => $this->estimated_size,
            'size_unit' => $this->size_unit,
            'retention_period' => $this->retention_period,
            'primary_languages' => $this->primary_languages,
            'contains_personal_data' => $this->contains_personal_data,
            'sensitivity' => $this->sensitivity,
            'cross_border_transfer' => $this->cross_border_transfer,
            'license_type' => $this->license_type,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'pivot' => $this->whenPivotLoaded('dataset_element', function () {
                $pivot = $this->pivot;

                return [
                    'dataset_id' => $pivot->dataset_id,
                    'data_element_id' => $pivot->data_element_id,
                    'column_name' => $pivot->column_name,
                    'nullable' => $pivot->nullable,
                    'sensitivity_override' => $pivot->sensitivity_override,
                    'pii_override' => $pivot->pii_override,
                    'transform_applied' => $pivot->transform_applied,
                    'quality_rules_applied' => $pivot->quality_rules_applied,
                    'cde_in_dataset' => $pivot->cde_in_dataset,
                    'cde_category_in_dataset' => $pivot->cde_category_in_dataset,
                    'lineage_source_column' => $pivot->lineage_source_column,
                    'deprecated' => $pivot->deprecated,
                ];
            }),
        ];
    }
}
