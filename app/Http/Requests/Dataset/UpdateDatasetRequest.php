<?php

namespace App\Http\Requests\Dataset;

use App\Enums\Dataset\ContainsPii;
use App\Enums\Dataset\ControllerRole;
use App\Enums\Dataset\CrossBorderTransfer;
use App\Enums\Dataset\DataStructure;
use App\Enums\Dataset\DataSubjectCategory;
use App\Enums\Dataset\LawfulBasis;
use App\Enums\Dataset\LicenseType;
use App\Enums\Dataset\Purpose;
use App\Enums\Dataset\Sensitivity;
use App\Enums\Dataset\StorageFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'source_ids' => ['sometimes', 'array'],
            'source_ids.*' => ['integer', 'exists:data_sources,id'],
            'purpose' => ['sometimes', 'array'],
            'purpose.*' => ['string', Rule::in(array_map(fn($c) => $c->value, Purpose::cases()))],
            'schema_summary' => ['nullable', 'string'],
            'sensitivity' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, Sensitivity::cases()))],
            'contains_pii' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, ContainsPii::cases()))],
            'data_subject_categories' => ['sometimes', 'array'],
            'data_subject_categories.*' => [Rule::in(array_map(fn($c) => $c->value, DataSubjectCategory::cases()))],
            'controller_role' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, ControllerRole::cases()))],
            'lawful_basis' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, LawfulBasis::cases()))],
            'lawful_basis_detail' => ['nullable', 'string'],
            'consent_required' => ['required_if:lawful_basis,Consent', 'boolean'],
            'consent_coverage_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'consent_source_ref' => ['nullable', 'string', 'max:255'],
            'licensing_basis' => ['nullable', 'string', 'max:255'],
            'license_type' => ['nullable', Rule::in(array_map(fn($c) => $c->value, LicenseType::cases()))],
            'privacy_notice_ref' => ['nullable', 'string', 'max:255'],
            'cross_border_transfer' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, CrossBorderTransfer::cases()))],
            'data_structure' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, DataStructure::cases()))],
            'storage_format' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, StorageFormat::cases()))],
            'content_types' => ['sometimes', 'array'],
            'content_types.*' => ['string'],
            'retention_policy_ref' => ['nullable', 'string', 'max:255'],
            'dpia_ref' => ['nullable', 'string', 'max:255'],
            'aia_ref' => ['nullable', 'string', 'max:255'],
            'owner_team' => ['sometimes', 'string', 'max:255'],
            'refresh_cadence' => ['nullable', 'string', 'max:255'],
            'quality_SLA' => ['nullable', 'string', 'max:255'],
            'catalog_asset_id' => ['nullable', 'string', 'max:255'],
            'catalog_uri' => ['nullable', 'string', 'max:255', 'url'],
        ];
    }
}
