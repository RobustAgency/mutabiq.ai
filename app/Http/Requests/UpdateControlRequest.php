<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Enums\Control\TestingMethod;
use App\Enums\Control\TestingFrequency;
use Illuminate\Foundation\Http\FormRequest;

class UpdateControlRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'reference' => ['sometimes', 'required', 'string', 'max:255', 'unique:controls,reference'],
            'objective' => ['sometimes', 'nullable', 'string'],
            'testing_method' => ['sometimes', 'required', Rule::in(array_map(fn ($case) => $case->value, TestingMethod::cases()))],
            'testing_frequency' => ['sometimes', 'required', Rule::in(array_map(fn ($case) => $case->value, TestingFrequency::cases()))],
            'evidence_expectations' => ['sometimes', 'nullable', 'string'],
            'applicability_criteria' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::in(array_map(fn ($case) => $case->value, TestingFrequency::cases()))],
            'last_test_date' => ['sometimes', 'nullable', 'date'],
            'next_test_due' => ['sometimes', 'nullable', 'date', 'after_or_equal:last_test_date'],
        ];
    }
}
