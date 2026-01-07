<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DataElement
 *
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
            'data_steward' => $this->data_steward,
            'status' => $this->status,
            'data_source_id' => $this->data_source_id,
            'database_name' => $this->database_name,
            'schema_name' => $this->schema_name,
            'table_name' => $this->table_name,
            'column_name' => $this->column_name,
            'used_in_datasets' => $this->used_in_datasets,
            'is_nullable' => $this->is_nullable,
            'is_unique' => $this->is_unique,
            'default_value' => $this->default_value,
            'validation_rule' => $this->validation_rule,
            'sample_values' => $this->sample_values,
            'data_type' => $this->data_type,
            'format' => $this->format,
            'sensitivity' => $this->sensitivity,
            'contains_personal_data' => $this->contains_personal_data,
            'personal_data_type' => $this->personal_data_type,
            'contains_sensitive_data' => $this->contains_sensitive_data,
            'default_masking_method' => $this->default_masking_method,
            'cde_flag' => $this->cde_flag,
            'cde_categories' => $this->cde_categories,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'data_source' => new DataSourceResource($this->whenLoaded('dataSource')),
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
