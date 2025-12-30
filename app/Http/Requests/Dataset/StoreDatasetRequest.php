<?php

namespace App\Http\Requests\Dataset;

use App\Enums\Dataset\Status;
use App\Enums\Dataset\Purpose;
use App\Enums\Dataset\SizeUnit;
use Illuminate\Validation\Rule;
use App\Enums\Dataset\OwnerTeam;
use App\Enums\Dataset\DataSteward;
use App\Enums\Dataset\LicenseType;
use App\Enums\Dataset\Sensitivity;
use App\Enums\Dataset\PrimaryLanguage;
use App\Enums\Dataset\ContainPersonalData;
use App\Enums\Dataset\CrossBorderTransfer;
use Illuminate\Foundation\Http\FormRequest;

class StoreDatasetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'purpose' => ['required', Rule::enum(Purpose::class)],
            'owner_team' => ['required', Rule::enum(OwnerTeam::class)],
            'data_steward' => ['required', Rule::enum(DataSteward::class)],
            'source_ids' => ['required', 'array'],
            'source_ids.*' => ['required', 'integer', 'exists:data_sources,id'],
            'status' => ['required', Rule::enum(Status::class)],
            'estimated_row_count' => ['nullable', 'integer', 'min:0'],
            'estimated_size' => ['nullable', 'integer', 'min:0'],
            'size_unit' => [
                'nullable',
                'required_with:estimated_size',
                Rule::enum(SizeUnit::class),
            ],
            'retention_period' => ['nullable', 'string', 'max:100'],
            'primary_languages' => ['nullable', 'array', 'min:1'],
            'primary_languages.*' => [Rule::enum(PrimaryLanguage::class)],
            'contains_personal_data' => ['required', Rule::enum(ContainPersonalData::class)],
            'sensitivity' => ['required', Rule::enum(Sensitivity::class)],
            'cross_border_transfer' => ['required', Rule::enum(CrossBorderTransfer::class)],
            'license_type' => ['nullable', Rule::enum(LicenseType::class)],
        ];
    }
}
