<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiModel extends Model
{
    // Use the SoftDeletes trait for soft deletion functionality
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
