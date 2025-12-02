<?php

namespace App\Http\Requests;

use App\Enums\Control\Status;
use Illuminate\Validation\Rule;
use App\Enums\Control\TestingMethod;
use App\Enums\Control\TestingFrequency;
use Illuminate\Foundation\Http\FormRequest;

class StoreControlRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'reference' => ['required', 'string', 'max:255', 'unique:controls,reference'],
            'objective' => ['nullable', 'string'],
            'testing_method' => ['required', Rule::in(array_map(fn ($case) => $case->value, TestingMethod::cases()))],
            'testing_frequency' => ['required', Rule::in(array_map(fn ($case) => $case->value, TestingFrequency::cases()))],
            'evidence_expectations' => ['nullable', 'string'],
            'applicability_criteria' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, Status::cases()))],
            'last_test_date' => ['nullable', 'date'],
            'next_test_due' => ['nullable', 'date', 'after_or_equal:last_test_date'],
        ];
    }
}
