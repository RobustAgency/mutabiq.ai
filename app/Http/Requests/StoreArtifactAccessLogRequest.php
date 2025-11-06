<?php

namespace App\Http\Requests;

use App\Enums\ArtifactAccessLog\AccessAction;
use App\Enums\ArtifactAccessLog\AccessContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArtifactAccessLogRequest extends FormRequest
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
            'artifact_id' => ['required', 'integer', Rule::exists('ai_model_artifacts', 'id')],
            'accessor_stakeholder_id' => ['required', 'integer', Rule::exists('stakeholders', 'id')],
            'action' => ['required', Rule::in(array_map(fn($c) => $c->value, AccessAction::cases()))],
            'context' => ['required', Rule::in(array_map(fn($c) => $c->value, AccessContext::cases()))],
            'ts' => ['required', 'timezone'],
            'ip_or_agent' => ['nullable', 'string', 'max:255'],
            'request_id' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
