<?php

namespace App\Http\Requests\IncidentAction;

use App\Enums\IncidentAction\ActionType;
use App\Enums\IncidentAction\ValidationResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentActionRequest extends FormRequest
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
            'ai_incident_id' => ['sometimes', 'required', 'integer', 'exists:ai_incidents,id'],
            'action_type' => ['sometimes', 'required', Rule::in(array_map(fn($c) => $c->value, ActionType::cases()))],
            'description' => ['sometimes', 'required', 'string'],
            'performed_by' => ['sometimes', 'required', 'string', 'max:255'],
            'started_at' => ['sometimes', 'required', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'validation_result' => ['sometimes', 'required', Rule::in(array_map(fn($c) => $c->value, ValidationResult::cases()))],
            'validation_notes' => ['nullable', 'string'],
            'linked_release_id' => ['nullable', 'string', 'max:255'],
            'evidence_link' => ['nullable', 'url', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'completed_at.after_or_equal' => 'The completed at must be after or equal to started at.',
        ];
    }
}
