<?php

namespace App\Observers;

use App\Models\PrivacyIncident;

class PrivacyIncidentObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'incident_code',
            'incident_title',
            'incident_type',
            'risk_level',
            'is_breach',
            'breach_criteria_met',
            'detected_date',
            'occurred_date',
            'notification_deadline',
            'hours_to_deadline',
            'is_deadline_passed',
            'incident_description',
            'what_happened',
            'how_discovered',
            'data_compromised',
            'data_categories_affected',
            'estimated_affected_subjects',
            'affected_subject_keys',
            'notification_required',
            'notification_status',
            'authority_notified',
            'authority_notification_date',
            'supervisory_authority',
            'authority_response',
            'subjects_notified',
            'subject_notification_date',
            'notification_method',
            'notification_template_used',
            'immediate_actions',
            'mitigation_measures',
            'preventive_measures',
            'root_cause_analysis',
            'responsible_party',
            'lessons_learned',
        ];
    }

    public function created(PrivacyIncident $incident): void
    {
        $this->logCreate($incident);
    }

    public function updating(PrivacyIncident $incident): void
    {
        $this->logUpdate($incident, $incident->getOriginal());
    }

    public function deleted(PrivacyIncident $incident): void
    {
        $this->logDelete($incident);
    }
}
