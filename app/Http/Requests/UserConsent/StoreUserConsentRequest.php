<?php

namespace App\Http\Requests\UserConsent;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\ConsentStatus;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\LegalBasis;
use App\Enums\UserConsent\SubjectRealm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_key' => ['required', 'string', 'max:255'],
            'subject_realm' => ['required', Rule::enum(SubjectRealm::class)],
            'jurisdiction' => ['required', Rule::enum(Jurisdiction::class)],
            'consent_purpose' => ['required', 'array', 'min:1'],
            'consent_purpose.*' => [Rule::enum(ConsentPurpose::class)],
            'consent_status' => ['required', Rule::enum(ConsentStatus::class)],
            'legal_basis' => ['required', Rule::enum(LegalBasis::class)],
            'source_system' => ['required', 'string', 'max:255'],
            'evidence_ref' => ['required', 'string', 'max:255'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'scope' => ['nullable', 'string'],
        ];
    }
}
