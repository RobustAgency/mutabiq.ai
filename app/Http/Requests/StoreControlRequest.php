<?php

namespace App\Http\Requests;

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
            'code' => ['required', 'string', 'max:100', 'unique:controls,code'],
            'question' => ['nullable', 'string'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'framework_ids' => ['array'],
            'framework_ids.*' => ['exists:frameworks,id'],
            'requirement_ids' => ['array'],
            'requirement_ids.*' => ['exists:requirements,id'],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['exists:tags,id'],
        ];
    }
}
