<?php

namespace App\Models;

use Database\Factories\AiIncidentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiIncident extends Model
{
    /** @use HasFactory<AiIncidentFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'title',
        'summary',
        'category',
        'severity',
        'status',
        'stage',
        'ic_owner',
        'ai_model_id',
        'ai_model_version_id',
        'use_case_id',
        'first_seen_at',
        'declared_at',
        'resolved_at',
        'closed_at',
        'impacted_users',
        'impacted_data',
        'impacted_systems',
        'linked_release_id',
        'linked_risk_id',
        'linked_assessment_id',
        'linked_capa_id',
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
            'first_seen_at' => 'datetime',
            'declared_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'impacted_data' => 'array',
        ];
    }
}
