<?php

namespace App\Http\Requests\ConsentScope;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsentScopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dataset_id' => ['required', 'integer', 'exists:datasets,id'],
            'purpose' => ['required', 'array'],
            'purpose.*' => ['string', Rule::in(array_map(fn($c) => $c->value, ConsentPurpose::cases()))],
            'subject_realm' => ['required', Rule::enum(SubjectRealm::class)],
            'jurisdiction' => ['required', Rule::enum(Jurisdiction::class)],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }
}
