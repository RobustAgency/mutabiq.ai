<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataProtectionImpactAssessment extends Model
{
    protected $fillable = [
        'dpia_code',
        'dpia_name',
        'ropo_id',
        'linked_ai_model_id',
        'linked_asset_type',
        'automated_trigger',
        'trigger_reason',
        'risk_level',
        'risk_score',
        'stage',
        'completion_percentage',
        'necessity_justification',
        'proportionality_assessment',
        'alternatives_considered',
        'identified_risks',
        'likelihood_assessment',
        'impact_assessment',
        'mitigation_measures',
        'residual_risk_level',
        'dpo_consulted',
        'dpo_consultation_date',
        'dpo_advice',
        'dpo_user_id',
        'stakeholders_consulted',
        'stakeholder_feedback',
        'data_subjects_consulted',
        'consultation_method',
        'final_decision',
        'approval_date',
        'approved_by',
        'conditions',
        'status',
        'review_frequency_months',
        'next_review_date',
        'applicable_jurisdictions',
        'created_by',
        'updated_by',
        'version',
    ];

    protected $casts = [
        'automated_trigger' => 'boolean',
        'applicable_jurisdictions' => 'array',
        'stakeholders_consulted' => 'array',
        'data_subjects_consulted' => 'boolean',
        'dpo_consultation_date' => 'date',
        'next_review_date' => 'date',
        'approval_date' => 'date',
    ];
}
