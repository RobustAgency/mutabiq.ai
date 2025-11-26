<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class AiModel
 *
 * @property-read string $display_id
 */
class AiModel extends Model
{
    /** @use HasFactory<\Database\Factories\AiModelFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'category',
        'type',
        'technical_domain',
        'purpose',
        'criticality_level',
        'regulatory_risk_tier',
        'eu_ai_category',
        'ownership_category',
        'responsible_org_role',
        'business_owner_id',
        'custodian_id',
        'business_adoption_status',
        'current_version_id',
        'created_by',
        'updated_by',
    ];

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the user that created the AI model.
     *
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the AI model.
     *
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the organization that owns this AI model.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the display ID attribute.
     */
    public function getDisplayIdAttribute(): string
    {
        return 'AI-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
