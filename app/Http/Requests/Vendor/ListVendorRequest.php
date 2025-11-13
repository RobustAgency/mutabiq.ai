<?php

namespace App\Http\Requests\Vendor;

use App\Enums\Vendor\RiskTier;
use App\Enums\Vendor\VendorStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListVendorRequest extends FormRequest
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
            'risk_tier' => ['nullable', 'string', Rule::enum(RiskTier::class)],
            'status' => ['nullable', 'string', Rule::enum(VendorStatus::class)],
            'owner' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date', 'before_or_equal:today'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
