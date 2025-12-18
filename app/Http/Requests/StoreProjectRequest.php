<?php

namespace App\Http\Requests;

use App\Enums\GovernancePillar;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'governance_pillar' => [
                'required',
                Rule::in(array_map(fn ($c) => $c->value, GovernancePillar::cases())),
            ],
        ];
    }
}
