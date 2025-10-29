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
        'source_organization_id',
        'custodian_id',
        'vendor_id',
        'primary_category',
        'type',
        'domain_specialization',
        'operational_status',
        'business_status',
        'total_versions',
        'strategic_importance',
        'regulatory_risk_classification',
        'organizational_role',
        'ownership_type',
        'current_owner',
        'development_source',
        'created_by',
        'updated_by',
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
     * Get the custodian stakeholder associated with the AI model.
     * 
     * @return BelongsTo<Stakeholder, $this>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'custodian_id');
    }

    /**
     * Get the source organization stakeholder associated with the AI model.
     * 
     * @return BelongsTo<Stakeholder, $this>
     */
    public function source_organization(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'source_organization_id');
    }
}
