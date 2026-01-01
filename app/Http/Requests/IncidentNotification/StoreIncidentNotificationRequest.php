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

class StoreIncidentNotificationRequest extends FormRequest
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
            'template' => ['nullable', Rule::enum(Template::class)],
            'language' => ['nullable', Rule::enum(Language::class)],
            'regulatory_basis' => ['nullable', Rule::enum(RegulatoryBasis::class)],
            'notification_deadline' => ['nullable', 'date'],
            'audience_type' => ['required', Rule::enum(AudienceType::class)],
            'channel' => ['required', Rule::enum(Channel::class)],
            'notice_summary' => ['required', 'string'],
            'notice_link' => ['nullable', 'url', 'max:2048'],
            'sent_at' => ['required', 'date'],
            'sent_by' => ['nullable', 'string', 'max:255'],
            'delivery_status' => ['required', Rule::enum(DeliveryStatus::class)],
            'response_summary' => ['nullable', 'string'],
            'follow_up_required' => ['required', 'boolean'],
            'follow_up_date' => ['nullable', 'date'],
            'follow_up_notes' => ['nullable', 'string'],
        ];
    }
}
