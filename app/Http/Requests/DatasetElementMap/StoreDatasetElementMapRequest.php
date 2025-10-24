<?php

namespace App\Http\Requests\DatasetElementMap;

use App\Enums\DataElement\CdeCategory;
use App\Enums\DatasetElementMap\CdeInDataset;
use App\Enums\DatasetElementMap\Deprecated;
use App\Enums\DatasetElementMap\Nullable;
use App\Enums\DatasetElementMap\PiiOverride;
use App\Enums\DatasetElementMap\SensitivityOverride;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDatasetElementMapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dataset_id' => ['required', 'integer', 'exists:datasets,id'],
            'data_element_id' => ['required', 'integer', 'exists:data_elements,id'],
            'column_name' => ['required', 'string', 'max:255'],
            'nullable' => ['required', 'string', Rule::enum(Nullable::class)],
            'sensitivity_override' => ['nullable', 'string', Rule::enum(SensitivityOverride::class)],
            'pii_override' => ['sometimes', 'string', Rule::enum(PiiOverride::class)],
            'transform_applied' => ['nullable', 'string', 'max:255'],
            'quality_rules_applied' => ['nullable', 'string'],
            'cde_in_dataset' => ['required', 'string', Rule::enum(CdeInDataset::class)],
            'cde_category_in_dataset' => ['nullable', 'string', Rule::enum(CdeCategory::class)],
            'lineage_source_column' => ['nullable', 'string', 'max:255'],
            'deprecated' => ['sometimes', 'string', Rule::enum(Deprecated::class)],
        ];
    }
}
