<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UseCase extends Model
{
    /** @use HasFactory<\Database\Factories\UseCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'problem_statement',
        'expected_business_value',
        'status',
        'business_domain',
        'roi_classification',
        'priority',
        'data_sensitivity',
        'expected_roi',
        'estimated_time_savings',
        'estimated_cost_savings',
        'estimated_revenue_impact',
        'success_metrics',
        'preliminary_risk_level',
        'regulatory_impact',
        'potential_harm',
        'human_oversight_mode',
        'dependencies',
        'budget_allocated',
        'target_deployment_date',
        'estimated_fte_saving',
        'data_availability_status',
        'data_readiness',
        'created_by',
        'updated_by',
        'business_owner_id',
        'technical_owner_id',
    ];

    protected $casts = [
        'roi_assessment' => 'boolean',
        'risk_assessment' => 'boolean',
        'data_assessment' => 'boolean',
        'estimated_time_savings' => 'decimal:2',
        'estimated_cost_savings' => 'decimal:2',
        'estimated_fte_saving' => 'integer',
        'estimated_revenue_impact' => 'decimal:2',
        'target_deployment_date' => 'date',
    ];

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the organization that owns this use case.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the business owner stakeholder.
     *
     * @return BelongsTo<Stakeholder, $this>
     */
    public function businessOwner(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'business_owner_id');
    }

    /**
     * Get the technical owner stakeholder.
     *
     * @return BelongsTo<Stakeholder, $this>
     */
    public function technicalOwner(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'technical_owner_id');
    }

    public function getDisplayIdAttribute(): string
    {
        return 'UC-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * The stakeholders that belong to the use case.
     *
     * @return BelongsToMany<Stakeholder, $this>
     */
    public function stakeholders(): BelongsToMany
    {
        return $this->belongsToMany(Stakeholder::class);
    }
}
