<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiRiskTreatment extends Model
{
    /** @use HasFactory<\Database\Factories\AiRiskTreatmentFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'ai_risk_register_id',
        'treatment_type',
        'plan_summary',
        'owner_stakeholder_id',
        'assignee',
        'due_date',
        'status',
        'expected_residual_level',
        'result_verification',
        'evidence_link',
        'linked_capa_id',
        'closed_at',
    ];

    protected $casts = [
        'assignee' => 'array',
        'due_date' => 'date',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the organization that owns the AiRiskTreatment.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the AI Risk Register that this treatment belongs to.
     *
     * @return BelongsTo<AiRiskRegister, $this>
     */
    public function aiRiskRegister(): BelongsTo
    {
        return $this->belongsTo(AiRiskRegister::class);
    }
}
