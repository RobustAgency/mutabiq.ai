<?php

namespace App\Http\Requests\Vendor;

use App\Enums\Vendor\RiskTier;
use App\Enums\Vendor\VendorStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRequest extends FormRequest
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
            'vendor_name' => ['sometimes', 'string', 'max:255'],
            'legal_name' => ['sometimes', 'string', 'max:255'],
            'hq_country' => ['sometimes', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'risk_tier' => ['sometimes', 'string', Rule::enum(RiskTier::class)],
            'status' => ['sometimes', 'string', Rule::enum(VendorStatus::class)],
            'stakeholder_id' => ['sometimes', 'integer', 'exists:stakeholders,id'],
            'primary_contacts' => ['nullable', 'array'],
            'primary_contacts.*.name' => ['required', 'string'],
            'primary_contacts.*.email' => ['required', 'email'],
            'primary_contacts.*.role' => ['nullable', 'string'],
            'primary_contacts.*.phone' => ['nullable', 'string'],
            'primary_contacts.*.primary' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
