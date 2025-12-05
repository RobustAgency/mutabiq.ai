<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KriIndicator extends Model
{
    /** @use HasFactory<\Database\Factories\KriIndicatorFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'ai_risk_register_id',
        'name',
        'definition',
        'directionality',
        'unit',
        'sample_window',
        'threshold_warning',
        'threshold_critical',
        'data_source',
        'collection_method',
        'frequency',
        'alert_routing',
        'action_on_breach',
        'status',
        'owner_team',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'alert_routing' => 'array',
    ];

    /**
     * Get the organization that owns the KRI indicator.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the AI risk register associated with the KRI indicator.
     *
     * @return BelongsTo<AiRiskRegister, $this>
     */
    public function aiRiskRegister(): BelongsTo
    {
        return $this->belongsTo(AiRiskRegister::class);
    }

    /**
     * Get the user who created the KRI indicator.
     *
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
