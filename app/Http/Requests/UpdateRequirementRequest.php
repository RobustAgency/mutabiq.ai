<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Enums\Requirement\Category;
use App\Enums\Requirement\Priority;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequirementRequest extends FormRequest
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
            'reference' => ['sometimes', 'string', 'max:255'],
            'requirement_text' => ['sometimes', 'nullable', 'string'],
            'category' => ['sometimes', Rule::in(array_map(fn ($c) => $c->value, Category::cases()))],
            'applicability' => ['sometimes', 'string', 'max:255'],
            'effective_from' => ['sometimes', 'nullable', 'date'],
            'effective_to' => ['sometimes', 'nullable', 'date'],
            'supersedes_req_id' => ['sometimes', 'nullable', 'exists:requirements,id'],
            'superseded_by_req_id' => ['sometimes', 'nullable', 'exists:requirements,id'],
            'priority' => ['sometimes', Rule::in(array_map(fn ($c) => $c->value, Priority::cases()))],
            'tags' => ['sometimes', 'nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'framework_ids' => ['sometimes', 'array'],
            'framework_ids.*' => ['exists:frameworks,id'],
        ];
    }
}
