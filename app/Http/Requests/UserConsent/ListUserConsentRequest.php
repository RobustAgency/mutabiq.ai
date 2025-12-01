<?php

namespace App\Http\Requests\UserConsent;

use Illuminate\Foundation\Http\FormRequest;

class ListUserConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'consent_status' => ['nullable', 'string', 'max:50'],
            'legal_basis' => ['nullable', 'string', 'max:50'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ];
    }
}
