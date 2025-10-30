<?php

namespace App\Http\Requests\ConsentCoverage;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConsentCoverageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dataset_id' => ['sometimes', 'integer', 'exists:datasets,id'],
            'snapshot_id' => ['nullable', 'integer', 'exists:dataset_snapshots,id'],
            'purpose' => ['sometimes', 'array'],
            'purpose.*' => ['string', Rule::in(array_map(fn($c) => $c->value, ConsentPurpose::cases()))],
            'jurisdiction' => ['sometimes', Rule::enum(Jurisdiction::class)],
            'as_of' => ['sometimes', 'date'],
            'subjects_total' => ['sometimes', 'integer', 'min:0'],
            'subjects_with_valid_consent' => ['sometimes', 'integer', 'min:0', 'lte:subjects_total'],
            'coverage_pct' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'evidence_ref' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'subjects_with_valid_consent.lte' => 'Subjects with valid consent cannot exceed total subjects.',
        ];
    }
}
