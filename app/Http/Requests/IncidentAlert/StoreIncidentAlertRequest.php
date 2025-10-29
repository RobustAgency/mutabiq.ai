<?php

namespace App\Http\Requests\IncidentAlert;

use App\Enums\AlertSourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentAlertRequest extends FormRequest
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
            'ai_incident_id' => ['required', 'integer', 'exists:ai_incidents,id'],
            'source_type' => ['required', Rule::in(array_map(fn($c) => $c->value, AlertSourceType::cases()))],
            'source_ref' => ['nullable', 'string', 'max:255'],
            'rule_version' => ['nullable', 'string', 'max:255'],
            'context' => ['nullable', 'string'],
            'first_seen_at' => ['required', 'date'],
            'last_seen_at' => ['nullable', 'date', 'after_or_equal:first_seen_at'],
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
            'last_seen_at.after_or_equal' => 'The last seen at must be after or equal to first seen at.',
        ];
    }
}
