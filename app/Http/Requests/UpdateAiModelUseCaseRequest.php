<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiModelUseCaseRequest extends FormRequest
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
            'ai_model_id' => ['sometimes', 'exists:ai_models,id'],
            'use_case_id' => ['sometimes', 'exists:use_cases,id'],
            'ai_model_version_id' => ['sometimes', 'nullable', 'exists:ai_model_versions,id'],
            'relationship_type' => ['sometimes', 'string'],
        ];
    }
}
