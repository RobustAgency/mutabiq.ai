<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiRiskRegister extends Model
{
    /** @use HasFactory<\Database\Factories\AiRiskRegisterFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'title',
        'risk_category',
        'ai_model_id',
        'ai_model_version_id',
        'use_case_id',
        'description',
        'related_controls',
        'likelihood_code',
        'impact_code',
        'inherent_score',
        'residual_score',
        'risk_level',
        'decision',
        'risk_owner',
        'review_cadence',
        'next_review_due',
        'status',
        'linked_assessment_id',
        'linked_incident_id',
        'linked_capa_id',
        'evidence_link',
        'likelihood_label_snapshot',
        'impact_label_snapshot',
        'method_name_snapshot',
        'created_by',
    ];

    protected $casts = [
        'related_controls' => 'array',
        'next_review_due' => 'date',
    ];

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the organization that owns this risk.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the AI model associated with this risk.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }

    /**
     * Get the AI model version associated with this risk.
     *
     * @return BelongsTo<AiModelVersion, $this>
     */
    public function aiModelVersion(): BelongsTo
    {
        return $this->belongsTo(AiModelVersion::class);
    }

    /**
     * Get the use case associated with this risk.
     *
     * @return BelongsTo<UseCase, $this>
     */
    public function useCase(): BelongsTo
    {
        return $this->belongsTo(UseCase::class);
    }

    /**
     * Get the stakeholder who owns this risk.
     *
     * @return BelongsTo<Stakeholder, $this>
     */
    public function riskOwner(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'risk_owner');
    }

    public function getDisplayIdAttribute(): string
    {
        return 'ARR-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
