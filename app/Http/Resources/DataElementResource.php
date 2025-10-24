<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DataElement
 * @property mixed $pivot
 */
class DataElementResource extends JsonResource
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
            'business_definition' => $this->business_definition,
            'data_type' => $this->data_type,
            'format' => $this->format,
            'sensitivity' => $this->sensitivity,
            'pii_flag' => $this->pii_flag,
            'personal_data_category' => $this->personal_data_category,
            'special_category_flag' => $this->special_category_flag,
            'cde_flag' => $this->cde_flag,
            'cde_category' => $this->cde_category,
            'owner_team' => $this->owner_team,
            'quality_rules_ref' => $this->quality_rules_ref,
            'catalog_column_id' => $this->catalog_column_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'datasets' => DatasetResource::collection($this->whenLoaded('datasets')),
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
