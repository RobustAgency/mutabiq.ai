<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequirementRequest extends FormRequest
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
            'reference' => ['required', 'string', 'max:255'],
            'requirement_text' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'applicability' => ['required', 'string', 'max:255'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date'],
            'supersedes_req_id' => ['nullable', 'exists:requirements,id'],
            'superseded_by_req_id' => ['nullable', 'exists:requirements,id'],
            'priority' => ['required', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'framework_ids' => ['required', 'array'],
            'framework_ids.*' => ['exists:frameworks,id'],
        ];
    }
}
