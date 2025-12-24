<?php

namespace App\Http\Requests\Vendor;

use App\Enums\Vendor\Type;
use App\Enums\Vendor\RiskTier;
use Illuminate\Validation\Rule;
use App\Enums\Vendor\VendorStatus;
use App\Enums\Vendor\DataProcessingRole;
use Illuminate\Foundation\Http\FormRequest;

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
            'type' => ['sometimes', 'array'],
            'type.*' => ['string', Rule::enum(Type::class)],
            'data_processing_role' => ['sometimes', 'string', Rule::enum(DataProcessingRole::class)],
            'service_provided' => ['nullable', 'string', 'max:500'],
            'primary_contacts' => ['nullable', 'array'],
            'primary_contacts.*.name' => ['required', 'string'],
            'primary_contacts.*.email' => ['required', 'email'],
            'primary_contacts.*.role' => ['nullable', 'string'],
            'primary_contacts.*.phone' => ['nullable', 'string'],
            'primary_contacts.*.primary' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'duns_number' => ['nullable', 'string', 'max:50'],
            'lei_number' => ['nullable', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'stock_ticker' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
