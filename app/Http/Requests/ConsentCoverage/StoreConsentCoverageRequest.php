<?php

namespace App\Http\Requests\ConsentCoverage;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsentCoverageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dataset_id' => ['required', 'integer', 'exists:datasets,id'],
            'snapshot_id' => ['nullable', 'integer', 'exists:dataset_snapshots,id'],
            'purpose' => ['required', 'array'],
            'purpose.*' => ['string', Rule::in(array_map(fn($c) => $c->value, ConsentPurpose::cases()))],
            'jurisdiction' => ['required', Rule::enum(Jurisdiction::class)],
            'as_of' => ['required', 'date'],
            'subjects_total' => ['required', 'integer', 'min:0'],
            'subjects_with_valid_consent' => ['required', 'integer', 'min:0', 'lte:subjects_total'],
            'coverage_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'evidence_ref' => ['required', 'string', 'max:255'],
            'source_created_at' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'subjects_with_valid_consent.lte' => 'Subjects with valid consent cannot exceed total subjects.',
        ];
    }
}
