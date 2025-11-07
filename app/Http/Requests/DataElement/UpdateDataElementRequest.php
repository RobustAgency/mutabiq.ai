<?php

namespace App\Http\Requests\DataElement;

use App\Enums\DataElement\CdeCategory;
use App\Enums\DataElement\CdeFlag;
use App\Enums\DataElement\DataType;
use App\Enums\DataElement\PersonalDataCategory;
use App\Enums\DataElement\PiiFlag;
use App\Enums\DataElement\Sensitivity;
use App\Enums\DataElement\SpecialCategoryFlag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDataElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'element_id' => ['sometimes', 'string', 'max:255', Rule::unique('data_elements', 'element_id')->ignore($this->route('dataElement'))],
            'name' => ['sometimes', 'string', 'max:255'],
            'business_definition' => ['nullable', 'string'],
            'data_type' => ['sometimes', 'string', Rule::enum(DataType::class)],
            'format' => ['nullable', 'string', 'max:255'],
            'sensitivity' => ['sometimes', 'string', Rule::enum(Sensitivity::class)],
            'pii_flag' => ['sometimes', 'string', Rule::enum(PiiFlag::class)],
            'personal_data_category' => ['nullable', 'string', Rule::enum(PersonalDataCategory::class)],
            'special_category_flag' => ['sometimes', 'string', Rule::enum(SpecialCategoryFlag::class)],
            'cde_flag' => ['sometimes', 'string', Rule::enum(CdeFlag::class)],
            'cde_category' => ['required_if:cde_flag,yes', 'string', Rule::enum(CdeCategory::class)],
            'owner_team' => ['nullable', 'string', 'max:255'],
            'quality_rules_ref' => ['nullable', 'string'],
            'catalog_column_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
