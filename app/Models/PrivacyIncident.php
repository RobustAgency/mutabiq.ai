<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyIncident extends Model
{
    protected $fillable = [
        'organization_id',
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
        'authority_reference_number',
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
        'status',
        'resolution_date',
        'days_to_resolution',
        'processing_activity_ids',
        'affected_systems',
        'third_party_involved',
        'vendor_id',
        'evidence_uris',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_breach' => 'boolean',
        'breach_criteria_met' => 'array',
        'detected_date' => 'date',
        'occurred_date' => 'date',
        'notification_deadline' => 'date',
        'is_deadline_passed' => 'boolean',
        'data_categories_affected' => 'array',
        'affected_subject_keys' => 'array',
        'authority_notified' => 'boolean',
        'authority_notification_date' => 'date',
        'subjects_notified' => 'boolean',
        'subject_notification_date' => 'date',
        'processing_activity_ids' => 'array',
        'affected_systems' => 'array',
        'third_party_involved' => 'boolean',
        'evidence_uris' => 'array',
        'resolution_date' => 'date',
    ];

    /**
     * Get the organization that owns the incident.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the vendor involved in the incident.
     *
     * @return BelongsTo<Vendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user who created the incident.
     *
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the incident.
     *
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
