<?php

namespace App\Http\Requests\IncidentAlert;

use Illuminate\Validation\Rule;
use App\Enums\IncidentAlert\AlertSeverity;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\IncidentAlert\AlertSourceType;

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
            'source_type' => ['required', Rule::enum(AlertSourceType::class)],
            'data_source_id' => ['nullable', 'integer', 'exists:data_sources,id'],
            'alert_sensitivity' => ['required', Rule::enum(AlertSeverity::class)],
            'source_ref' => ['nullable', 'string', 'max:255'],
            'context' => ['required', 'string'],
            'first_seen_at' => ['required', 'date'],
            'last_seen_at' => ['nullable', 'date', 'after_or_equal:first_seen_at'],
            'evidence_link' => ['nullable', 'url', 'max:2048'],
            'auto_promote_incident' => ['nullable', 'boolean'],
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
