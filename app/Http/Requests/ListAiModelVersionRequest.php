<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAiModelVersionRequest extends FormRequest
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
            'ai_model_id' => ['sometimes', 'integer', 'exists:ai_models,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'version_type' => ['nullable', 'string', 'max:50'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'source_type' => ['nullable', 'string', 'max:100'],
            'lifecycle_stage' => ['nullable', 'string', 'max:50'],
            'release_role' => ['nullable', 'string', 'max:50'],
            'deployment_status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
