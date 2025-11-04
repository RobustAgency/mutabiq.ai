<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiModel extends Model
{
    /** @use HasFactory<\Database\Factories\AiModelFactory> */
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'organization_id',
        'source_org_stakeholder_id',
        'organizational_role',
        'owner_stakeholder_id',
        'vendor_id',
        'current_version_id',
        'primary_category',
        'type',
        'domain_specialization',
        'operational_status',
        'business_status',
        'regulatory_risk_classification',
        'ownership_type',
        'development_source',
        'created_by',
        'updated_by',
        'creator_email',
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
     * Get the vendor associated with the AI model.
     * 
     * @return BelongsTo<Vendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get the source organization stakeholder.
     * 
     * @return BelongsTo<Stakeholder, $this>
     */
    public function sourceOrgStakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'source_org_stakeholder_id');
    }

    /**
     * Get the owner stakeholder.
     * 
     * @return BelongsTo<Stakeholder, $this>
     */
    public function ownerStakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'owner_stakeholder_id');
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
}
