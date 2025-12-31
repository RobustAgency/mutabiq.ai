<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\AiIncidentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiIncident extends Model
{
    /** @use HasFactory<AiIncidentFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'title',
        'summary',
        'incident_type',
        'domain',
        'severity',
        'status',
        'incident_commander',
        'response_team',
        'primary_regulatory_framework',
        'notification_requirement',
        'data_residency_affected',
        'regulatory_reference',
        'estimated_impacted_users',
        'estimated_impacted_records',
        'data_types_impacted',
        'affected_business_units',
        'external_parties_involved',
        'business_impact_description',
        'impacted_systems',
        'ai_model_id',
        'linked_dataset_id',
        'linked_risk_id',
        'linked_assessment_id',
        'evidence_link',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_types_impacted' => 'array',
            'affected_business_units' => 'array',
            'external_parties_involved' => 'array',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    public function getDisplayIdAttribute(): string
    {
        return 'AI-INC-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
