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
        'source_organization',
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
}
