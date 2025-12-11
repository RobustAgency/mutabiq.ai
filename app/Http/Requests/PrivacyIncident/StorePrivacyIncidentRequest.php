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

class StorePrivacyIncidentRequest extends FormRequest
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
            'incident_title' => ['required', 'string', 'max:255'],
            'incident_type' => ['required', Rule::enum(IncidentType::class)],
            'risk_level' => ['required', Rule::enum(RiskLevel::class)],
            'is_breach' => ['required', 'boolean'],
            'breach_criteria_met' => [
                'required_if:is_breach,true',
                'array',
                'min:1',
            ],
            'detected_date' => ['required', 'date'],
            'occurred_date' => ['nullable', 'date'],
            'hours_to_deadline' => ['nullable', 'integer'],
            'is_deadline_passed' => ['nullable', 'boolean'],
            'incident_description' => ['required', 'string'],
            'what_happened' => ['required', 'string'],
            'how_discovered' => ['required', 'string'],
            'data_compromised' => ['required', 'string'],
            'data_categories_affected' => ['required', 'array', 'min:1'],
            'data_categories_affected.*' => [
                'string',
                Rule::enum(DataCategory::class),
            ],
            'estimated_affected_subjects' => ['required', 'integer'],
            'affected_subject_keys' => ['nullable', 'array'],
            'notification_required' => ['required', Rule::enum(NotificationRequired::class)],
            'notification_status' => ['required', Rule::enum(NotificationStatus::class)],
            'authority_notified' => ['required', 'boolean'],
            'authority_notification_date' => ['required_if:authority_notified,true', 'date'],
            'supervisory_authority' => ['required_if:authority_notified,true', 'string'],
            'authority_reference_number' => ['nullable', 'string'],
            'authority_response' => ['nullable', 'string'],
            'subjects_notified' => ['required', 'boolean'],
            'subject_notification_date' => ['required_if:subjects_notified,true', 'date'],
            'notification_method' => [
                'required_if:subjects_notified,true',
                Rule::enum(NotificationMethod::class),
            ],
            'notification_template_used' => ['nullable', 'string'],
            'immediate_actions' => ['required', 'string'],
            'mitigation_measures' => ['required', 'string'],
            'preventive_measures' => ['required', 'string'],
            'root_cause_analysis' => ['required_if:status,'.Status::RESOLVED->value, 'string'],
            'responsible_party' => ['nullable', 'string'],
            'lessons_learned' => ['required_if:status,'.Status::RESOLVED->value, 'string'],
            'status' => ['required', Rule::enum(Status::class)],
            'resolution_date' => ['required_if:status,'.Status::RESOLVED->value, 'date'],
            'processing_activity_ids' => ['nullable', 'array'],
            'processing_activity_ids.*' => ['integer', 'exists:record_of_processing_activities,id'],
            'affected_systems' => ['required', 'array'],
            'affected_systems.*' => ['string', 'max:255'],
            'third_party_involved' => ['required', 'boolean'],
            'vendor_id' => [
                'required_if:third_party_involved,true',
                'integer',
                'exists:vendors,id',
            ],
            'evidence_uris' => ['nullable', 'array'],
            'evidence_uris.*' => ['string', 'url'],
        ];
    }
}
