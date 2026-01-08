<?php

namespace App\Http\Requests\DataElement;

use Illuminate\Validation\Rule;
use App\Enums\DataElement\Status;
use App\Enums\DataElement\DataType;
use App\Enums\DataElement\DataSteward;
use App\Enums\DataElement\Sensitivity;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DataElement\DefaultMaskingMethod;
use App\Enums\DataElement\PersonalDataCategory;

class StoreDataElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'data_type' => ['required', 'string', Rule::enum(DataType::class)],
            'format' => ['nullable', 'string', 'max:255'],
            'business_definition' => ['required', 'string'],
            'data_steward' => ['required', Rule::enum(DataSteward::class)],
            'status' => ['required', 'string', Rule::enum(Status::class)],
            'data_source_id' => ['required', 'integer', 'exists:data_sources,id'],
            'database_name' => ['required', 'string', 'max:255'],
            'schema_name' => ['nullable', 'string', 'max:255'],
            'table_name' => ['required', 'string', 'max:255'],
            'column_name' => ['required', 'string', 'max:255'],
            'used_in_datasets' => ['nullable', 'array'],
            'is_nullable' => ['nullable', 'boolean'],
            'is_unique' => ['nullable', 'boolean'],
            'default_value' => ['nullable', 'string'],
            'validation_rule' => ['nullable', 'string'],
            'sample_values' => ['nullable', 'string'],
            'sensitivity' => ['required', 'string', Rule::enum(Sensitivity::class)],
            'contains_personal_data' => ['required', 'boolean'],
            'personal_data_type' => ['required_if:contains_personal_data,1', 'string', Rule::enum(PersonalDataCategory::class)],
            'contains_sensitive_data' => ['required_if:contains_personal_data,1', 'boolean'],
            'default_masking_method' => ['nullable', Rule::enum(DefaultMaskingMethod::class)],
            'cde_flag' => ['nullable', 'boolean'],
            'cde_categories' => ['required', 'array'],
        ];
    }
}
