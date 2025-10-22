<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\AiModelUseCase\RelationshipType;

class StoreAiModelUseCaseRequest extends FormRequest
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
            'ai_model_id' => ['required', 'exists:ai_models,id'],
            'use_case_id' => ['required', 'exists:use_cases,id'],
            'ai_model_version_id' => ['nullable', 'exists:ai_model_versions,id'],
            'relationship_type' => ['required', Rule::in(array_map(fn($c) => $c->value, RelationshipType::cases()))],
        ];
    }
}
