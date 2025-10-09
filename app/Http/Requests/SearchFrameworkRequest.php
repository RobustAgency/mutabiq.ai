<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchFrameworkRequest extends FormRequest
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
            'name' => ['string', 'min:2'],
            'status' => ['string', 'nullable'],
            'per_page' => ['integer', 'min:1'],
            'type' => ['string', 'nullable'],
            'authority_publisher' => ['string', 'nullable'],
            'binding_level' => ['string', 'nullable'],
            'sector_applicability' => ['string', 'nullable'],
            'risk_class_coverage' => ['string', 'nullable'],
            'certification_attestation' => ['string', 'nullable'],
            'assessment_mode' => ['string', 'nullable'],
        ];
    }
}