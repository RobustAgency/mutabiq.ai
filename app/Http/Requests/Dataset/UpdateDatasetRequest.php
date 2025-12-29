<?php

namespace App\Http\Requests\Dataset;

use App\Enums\Dataset\Status;
use App\Enums\Dataset\Purpose;
use App\Enums\Dataset\SizeUnit;
use Illuminate\Validation\Rule;
use App\Enums\Dataset\DataSteward;
use App\Enums\Dataset\LicenseType;
use App\Enums\Dataset\Sensitivity;
use App\Enums\Dataset\PrimaryLanguage;
use App\Enums\Dataset\ContainPersonalData;
use App\Enums\Dataset\CrossBorderTransfer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDatasetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'purpose' => ['sometimes', Rule::enum(Purpose::class)],
            'owner_team' => ['sometimes', 'string', 'max:255'],
            'data_steward' => ['sometimes', Rule::enum(DataSteward::class)],
            'source_ids' => ['sometimes', 'array'],
            'source_ids.*' => ['required', 'integer', 'exists:data_sources,id'],
            'status' => ['sometimes', Rule::enum(Status::class)],
            'estimated_row_count' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'estimated_size' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'size_unit' => [
                'sometimes',
                'nullable',
                'required_with:estimated_size',
                Rule::enum(SizeUnit::class),
            ],
            'retention_period' => ['sometimes', 'nullable', 'string', 'max:100'],
            'primary_languages' => ['sometimes', 'nullable', 'array', 'min:1'],
            'primary_languages.*' => [Rule::enum(PrimaryLanguage::class)],
            'contains_personal_data' => ['sometimes', Rule::enum(ContainPersonalData::class)],
            'sensitivity' => ['sometimes', Rule::enum(Sensitivity::class)],
            'cross_border_transfer' => ['sometimes', Rule::enum(CrossBorderTransfer::class)],
            'license_type' => ['sometimes', 'nullable', Rule::enum(LicenseType::class)],
        ];
    }
}
