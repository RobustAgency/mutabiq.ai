<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
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
            'name' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'code' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('requirements', 'code')->ignore($this->route('requirement')),
            ],
            'description' => ['nullable', 'string'],
            'framework_ids' => ['sometimes', 'array'],
            'framework_ids.*' => ['exists:frameworks,id'],
        ];
    }
}
