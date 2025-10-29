<?php

namespace App\Http\Requests\IncidentNotification;

use App\Enums\IncidentNotification\AudienceType;
use App\Enums\IncidentNotification\Channel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentNotificationRequest extends FormRequest
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
            'audience_type' => ['sometimes', 'required', Rule::in(array_map(fn($c) => $c->value, AudienceType::cases()))],
            'channel' => ['sometimes', 'required', Rule::in(array_map(fn($c) => $c->value, Channel::cases()))],
            'notice_summary' => ['sometimes', 'required', 'string'],
            'notice_link' => ['nullable', 'url', 'max:2048'],
            'notified_at' => ['sometimes', 'required', 'date'],
            'approved_by' => ['nullable', 'string', 'max:255'],
            'approval_ref' => ['nullable', 'string', 'max:255'],
            'follow_up_required' => ['sometimes', 'required', 'boolean'],
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
            'approved_by.required' => 'Approval is required for external audiences (customers, regulator, vendor, media).',
        ];
    }
}
