<?php

namespace App\Http\Requests;

use App\Enums\ArtifactAccessLog\AccessAction;
use App\Enums\ArtifactAccessLog\AccessContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListArtifactAccessLogRequest extends FormRequest
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
            'artifact_id' => ['sometimes', 'integer', Rule::exists('ai_model_artifacts', 'id')],
            'accessor_stakeholder_id' => ['sometimes', 'integer', Rule::exists('stakeholders', 'id')],
            'action' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, AccessAction::cases()))],
            'context' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, AccessContext::cases()))],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
