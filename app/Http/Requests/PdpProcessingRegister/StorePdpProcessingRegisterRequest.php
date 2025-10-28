<?php

namespace App\Http\Requests\PdpProcessingRegister;

use App\Enums\Status;
use App\Enums\UserConsent\LegalBasis;
use App\Enums\UserConsent\SubjectRealm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePdpProcessingRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purpose' => ['required', 'string'],
            'controller_role' => ['required', 'string', 'max:255'],
            'data_subject_categories' => ['required', 'array', 'min:1'],
            'data_subject_categories.*' => ['required', 'string', 'max:255'],
            'personal_data_categories' => ['required', 'array', 'min:1'],
            'personal_data_categories.*' => ['required', 'string', 'max:255'],
            'lawful_basis' => ['required', 'string', 'max:255'],
            'lawful_basis_detail' => ['nullable', 'string'],
            'retention_policy_ref' => ['nullable', 'string', 'max:255'],
            'recipients' => ['nullable', 'array'],
            'recipients.*' => ['required', 'string', 'max:255'],
            'international_transfer_ref' => ['nullable', 'string', 'max:255'],
            'dpia_required_flag' => ['nullable', 'string', 'max:255'],
            'security_measures_ref' => ['nullable', 'string', 'max:255'],
            'owner_team' => ['required', 'string', 'max:255'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'string', 'max:255'],
        ];
    }
}
