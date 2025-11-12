<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskMethodology extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'likelihood_scale',
        'impact_scale',
        'matrix_rule',
        'acceptance_thresholds',
        'aggregation_logic',
        'review_policy',
        'effective_from',
        'effective_to',
        'owner_team',
        'source_created_at',
    ];

    protected $casts = [
        'likelihood_scale' => 'array',
        'impact_scale' => 'array',
        'matrix_rule' => 'array',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'source_created_at' => 'datetime',
    ];

    /**
     * Get the organization that owns this risk methodology.
     * 
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
