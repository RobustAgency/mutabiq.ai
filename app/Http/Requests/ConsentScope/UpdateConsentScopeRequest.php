<?php

namespace App\Http\Requests\ConsentScope;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConsentScopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dataset_id' => ['sometimes', 'integer', 'exists:datasets,id'],
            'purpose' => ['sometimes', 'array'],
            'purpose.*' => ['string', Rule::in(array_map(fn($c) => $c->value, ConsentPurpose::cases()))],
            'subject_realm' => ['sometimes', Rule::enum(SubjectRealm::class)],
            'jurisdiction' => ['sometimes', Rule::enum(Jurisdiction::class)],
            'effective_from' => ['sometimes', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }
}
