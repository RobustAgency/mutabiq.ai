<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PrivacyIncident
 */
class PrivacyIncidentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'incident_code' => $this->incident_code,
            'incident_title' => $this->incident_title,
            'incident_type' => $this->incident_type,
            'risk_level' => $this->risk_level,
            'is_breach' => $this->is_breach,
            'breach_criteria_met' => $this->breach_criteria_met,
            'detected_date' => $this->detected_date?->toIso8601String(),
            'occurred_date' => $this->occurred_date?->toIso8601String(),
            'notification_deadline' => $this->notification_deadline?->toIso8601String(),
            'hours_to_deadline' => $this->hours_to_deadline,
            'is_deadline_passed' => $this->is_deadline_passed,
            'incident_description' => $this->incident_description,
            'what_happened' => $this->what_happened,
            'how_discovered' => $this->how_discovered,
            'data_compromised' => $this->data_compromised,
            'data_categories_affected' => $this->data_categories_affected,
            'estimated_affected_subjects' => $this->estimated_affected_subjects,
            'affected_subject_keys' => $this->affected_subject_keys,
            'notification_required' => $this->notification_required,
            'notification_status' => $this->notification_status,
            'authority_notified' => $this->authority_notified,
            'authority_notification_date' => $this->authority_notification_date?->toIso8601String(),
            'supervisory_authority' => $this->supervisory_authority,
            'authority_reference_number' => $this->authority_reference_number,
            'authority_response' => $this->authority_response,
            'subjects_notified' => $this->subjects_notified,
            'subject_notification_date' => $this->subject_notification_date?->toIso8601String(),
            'notification_method' => $this->notification_method,
            'notification_template_used' => $this->notification_template_used,
            'immediate_actions' => $this->immediate_actions,
            'mitigation_measures' => $this->mitigation_measures,
            'preventive_measures' => $this->preventive_measures,
            'root_cause_analysis' => $this->root_cause_analysis,
            'responsible_party' => $this->responsible_party,
            'lessons_learned' => $this->lessons_learned,
            'status' => $this->status,
            'resolution_date' => $this->resolution_date?->toIso8601String(),
            'days_to_resolution' => $this->days_to_resolution,
            'processing_activity_ids' => $this->processing_activity_ids,
            'affected_systems' => $this->affected_systems,
            'third_party_involved' => $this->third_party_involved,
            'vendor_id' => $this->vendor_id,
            'evidence_uris' => $this->evidence_uris,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
