<?php

namespace App\Http\Requests;

use App\Enums\Control\Status;
use Illuminate\Validation\Rule;
use App\Enums\Control\TestingMethod;
use App\Enums\Control\TestingFrequency;
use Illuminate\Foundation\Http\FormRequest;

class SearchControlRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'min:2'],
            'status' => ['sometimes', Rule::in(array_map(fn ($case) => $case->value, Status::cases()))],
            'testing_method' => ['sometimes', Rule::in(array_map(fn ($case) => $case->value, TestingMethod::cases()))],
            'testing_frequency' => ['sometimes', Rule::in(array_map(fn ($case) => $case->value, TestingFrequency::cases()))],
            'per_page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
