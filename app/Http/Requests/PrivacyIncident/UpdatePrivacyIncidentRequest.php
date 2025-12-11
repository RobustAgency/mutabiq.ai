<?php

namespace App\Http\Requests\PrivacyIncident;

use Illuminate\Validation\Rule;
use App\Enums\PrivacyIncident\Status;
use App\Enums\PrivacyIncident\RiskLevel;
use App\Enums\PrivacyIncident\IncidentType;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PrivacyIncident\NotificationMethod;
use App\Enums\PrivacyIncident\NotificationStatus;
use App\Enums\PrivacyIncident\NotificationRequired;
use App\Enums\RecordOfProcessingActivity\DataCategory;

class UpdatePrivacyIncidentRequest extends FormRequest
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
            'incident_title' => ['sometimes', 'string', 'max:255'],
            'incident_type' => ['sometimes', Rule::enum(IncidentType::class)],
            'risk_level' => ['sometimes', Rule::enum(RiskLevel::class)],
            'is_breach' => ['sometimes', 'boolean'],
            'breach_criteria_met' => [
                'sometimes',
                'array',
                'min:1',
            ],
            'detected_date' => ['sometimes', 'date'],
            'occurred_date' => ['sometimes', 'nullable', 'date'],
            'hours_to_deadline' => ['sometimes', 'nullable', 'integer'],
            'is_deadline_passed' => ['sometimes', 'nullable', 'boolean'],
            'incident_description' => ['sometimes', 'string'],
            'what_happened' => ['sometimes', 'string'],
            'how_discovered' => ['sometimes', 'string'],
            'data_compromised' => ['sometimes', 'string'],
            'data_categories_affected' => ['sometimes', 'array', 'min:1'],
            'data_categories_affected.*' => [
                'string',
                Rule::enum(DataCategory::class),
            ],
            'estimated_affected_subjects' => ['sometimes', 'integer'],
            'affected_subject_keys' => ['sometimes', 'nullable', 'array'],
            'notification_required' => ['sometimes', Rule::enum(NotificationRequired::class)],
            'notification_status' => ['sometimes', Rule::enum(NotificationStatus::class)],
            'authority_notified' => ['sometimes', 'boolean'],
            'authority_notification_date' => ['sometimes', 'required_if:authority_notified,true', 'date'],
            'supervisory_authority' => ['sometimes', 'required_if:authority_notified,true', 'string'],
            'authority_reference_number' => ['sometimes', 'nullable', 'string'],
            'authority_response' => ['sometimes', 'nullable', 'string'],
            'subjects_notified' => ['sometimes', 'boolean'],
            'subject_notification_date' => ['sometimes', 'required_if:subjects_notified,true', 'date'],
            'notification_method' => [
                'sometimes',
                'required_if:subjects_notified,true',
                Rule::enum(NotificationMethod::class),
            ],
            'notification_template_used' => ['sometimes', 'nullable', 'string'],
            'immediate_actions' => ['sometimes', 'string'],
            'mitigation_measures' => ['sometimes', 'string'],
            'preventive_measures' => ['sometimes', 'string'],
            'root_cause_analysis' => ['sometimes', 'required_if:status,'.Status::RESOLVED->value, 'string'],
            'responsible_party' => ['sometimes', 'nullable', 'string'],
            'lessons_learned' => ['sometimes', 'required_if:status,'.Status::RESOLVED->value, 'string'],
            'status' => ['sometimes', Rule::enum(Status::class)],
            'resolution_date' => ['sometimes', 'required_if:status,'.Status::RESOLVED->value, 'date'],
            'processing_activity_ids' => ['sometimes', 'nullable', 'array'],
            'processing_activity_ids.*' => ['integer', 'exists:record_of_processing_activities,id'],
            'affected_systems' => ['sometimes', 'array'],
            'affected_systems.*' => ['string', 'max:255'],
            'third_party_involved' => ['sometimes', 'boolean'],
            'vendor_id' => [
                'sometimes',
                'required_if:third_party_involved,true',
                'integer',
                'exists:vendors,id',
            ],
            'evidence_uris' => ['sometimes', 'nullable', 'array'],
            'evidence_uris.*' => ['string', 'url'],
        ];
    }
}
