<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFrameworkRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'code' => ['required', 'string', 'min:2', 'max:100', 'unique:frameworks,code'],
            'type' => ['required', 'string', 'max:100'],
            'geography' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string'],
            'version' => ['required', 'string', 'max:50'],
            'release_date' => ['nullable', 'date'],
            'is_published' => ['boolean'],
            'description' => ['nullable', 'string'],
            'authority_publisher' => ['nullable', 'string', 'max:255'],
            'binding_level' => ['nullable', 'string', 'max:100'],
            'sector_applicability' => ['nullable', 'string', 'max:255'],
            'risk_class_coverage' => ['nullable', 'string', 'max:255'],
            'certification_attestation' => ['nullable', 'string', 'max:255'],
            'assessment_mode' => ['nullable', 'string', 'max:255'],
            'framework_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ];
    }
}
