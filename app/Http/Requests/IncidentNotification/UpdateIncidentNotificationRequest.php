<?php

namespace App\Http\Requests\IncidentNotification;

use Illuminate\Validation\Rule;
use App\Enums\IncidentNotification\Channel;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\IncidentNotification\Language;
use App\Enums\IncidentNotification\Template;
use App\Enums\IncidentNotification\AudienceType;
use App\Enums\IncidentNotification\DeliveryStatus;
use App\Enums\IncidentNotification\RegulatoryBasis;

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
            'template' => ['sometimes', 'nullable', Rule::enum(Template::class)],
            'language' => ['sometimes', 'nullable', Rule::enum(Language::class)],
            'regulatory_basis' => ['sometimes', 'nullable', Rule::enum(RegulatoryBasis::class)],
            'notification_deadline' => ['sometimes', 'nullable', 'date'],
            'audience_type' => ['sometimes', 'required', Rule::enum(AudienceType::class)],
            'channel' => ['sometimes', 'required', Rule::enum(Channel::class)],
            'notice_summary' => ['sometimes', 'required', 'string'],
            'notice_link' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'sent_at' => ['sometimes', 'required', 'date'],
            'sent_by' => ['sometimes', 'nullable', 'string', 'max:255'],
            'delivery_status' => ['sometimes', 'required', Rule::enum(DeliveryStatus::class)],
            'response_summary' => ['sometimes', 'nullable', 'string'],
            'follow_up_required' => ['sometimes', 'required', 'boolean'],
            'follow_up_date' => ['sometimes', 'nullable', 'date'],
            'follow_up_notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
