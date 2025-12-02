<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Enums\Requirement\Category;
use App\Enums\Requirement\Priority;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequirementRequest extends FormRequest
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
            'category' => ['sometimes', Rule::in(array_map(fn ($c) => $c->value, Category::cases()))],
            'effective_from' => ['sometimes', 'date'],
            'effective_to' => ['sometimes', 'date'],
            'priority' => ['sometimes', Rule::in(array_map(fn ($p) => $p->value, Priority::cases()))],
            'per_page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
