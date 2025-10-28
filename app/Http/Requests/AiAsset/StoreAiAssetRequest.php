<?php

namespace App\Http\Requests\AiAsset;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiAssetRequest extends FormRequest
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
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'vendor_effective_from' => ['nullable', 'date'],
            'vendor_effective_to' => ['nullable', 'date', 'after:vendor_effective_from'],
            'vendor_agreement_id' => ['nullable', 'integer', 'exists:agreements,id'],
            'vendor_assessment_id' => ['nullable', 'integer'],
        ];
    }
}
