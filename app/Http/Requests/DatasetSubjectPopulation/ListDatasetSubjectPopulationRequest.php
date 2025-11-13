<?php

namespace App\Http\Requests\DatasetSubjectPopulation;

use Illuminate\Foundation\Http\FormRequest;

class ListDatasetSubjectPopulationRequest extends FormRequest
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
            'subject_realm' => ['sometimes', 'string', 'max:255'],
            'jurisdiction' => ['sometimes', 'string', 'max:255'],
            'from' => ['sometimes', 'date', 'before_or_equal:today'],
            'to' => ['sometimes', 'date', 'before_or_equal:today', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
