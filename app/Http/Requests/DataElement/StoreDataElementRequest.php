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
            'business_definition' => ['nullable', 'string'],
            'data_type' => ['required', 'string', Rule::in(array_map(fn(DataType $c) => $c->value, DataType::cases()))],
            'format' => ['nullable', 'string', 'max:255'],
            'sensitivity' => ['required', 'string', Rule::in(array_map(fn(Sensitivity $c) => $c->value, Sensitivity::cases()))],
            'pii_flag' => ['required', 'string', Rule::in(array_map(fn(PiiFlag $c) => $c->value, PiiFlag::cases()))],
            'personal_data_category' => ['nullable', 'string', Rule::in(array_map(fn(PersonalDataCategory $c) => $c->value, PersonalDataCategory::cases()))],
            'special_category_flag' => ['required', 'string', Rule::in(array_map(fn(SpecialCategoryFlag $c) => $c->value, SpecialCategoryFlag::cases()))],
            'cde_flag' => ['required', 'string', Rule::in(array_map(fn(CdeFlag $c) => $c->value, CdeFlag::cases()))],
            'cde_category' => ['nullable', 'string', Rule::in(array_map(fn(CdeCategory $c) => $c->value, CdeCategory::cases()))],
            'owner_team' => ['nullable', 'string', 'max:255'],
            'quality_rules_ref' => ['nullable', 'string'],
            'catalog_column_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
