<?php

namespace App\Http\Requests;

use App\Enums\OperationalStatus;
use App\Enums\OwnershipType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAiModelRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::enum(OperationalStatus::class)],
            'ownership_type' => ['nullable', 'string', Rule::enum(OwnershipType::class)],
            'regulatory_risk_classification' => ['nullable', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date', 'before_or_equal:today'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
