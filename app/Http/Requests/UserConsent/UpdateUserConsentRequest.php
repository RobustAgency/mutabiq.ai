<?php

namespace App\Http\Requests\UserConsent;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\ConsentStatus;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\LegalBasis;
use App\Enums\UserConsent\SubjectRealm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_key' => ['sometimes', 'string', 'max:255'],
            'subject_realm' => ['sometimes', Rule::enum(SubjectRealm::class)],
            'jurisdiction' => ['sometimes', Rule::enum(Jurisdiction::class)],
            'consent_purpose' => ['sometimes', 'array', 'min:1'],
            'consent_purpose.*' => [Rule::enum(ConsentPurpose::class)],
            'consent_status' => ['sometimes', Rule::enum(ConsentStatus::class)],
            'legal_basis' => ['sometimes', Rule::enum(LegalBasis::class)],
            'source_system' => ['sometimes', 'string', 'max:255'],
            'evidence_ref' => ['sometimes', 'string', 'max:255'],
            'effective_from' => ['sometimes', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'scope' => ['nullable', 'string'],
        ];
    }
}
