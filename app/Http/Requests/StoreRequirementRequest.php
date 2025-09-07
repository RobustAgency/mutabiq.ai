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
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:requirements,code'],
            'description' => ['nullable', 'string'],
            'framework_ids' => ['required', 'array'],
            'framework_ids.*' => ['exists:frameworks,id'],
        ];
    }
}
