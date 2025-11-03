<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UseCase extends Model
{
    /** @use HasFactory<\Database\Factories\UseCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'business_objective',
        'business_owner_id',
        'technical_owner_id',
        'business_domain',
        'roi_classification',
        'priority',
        'risk_level',
        'data_sensitivity',
        'expected_roi_percentage',
        'budget_allocated',
        'target_go_live_date',
        'status',
        'created_by',
        'updated_by',
        'roi_assessment',
        'risk_assessment',
        'data_assessment',
        'estimated_implementation_cost',
        'estimated_reduction_in_time',
        'estimated_reduction_in_cost',
        'estimated_revenue_increase',
        'estimated_fte_capacity_saving',
        'data_availability_status',
        'data_readiness',
    ];

    protected $casts = [
        'target_go_live_date' => 'date',
        'expected_roi_percentage' => 'decimal:2',
        'budget_allocated' => 'decimal:2',
        'estimated_implementation_cost' => 'decimal:2',
        'estimated_reduction_in_time' => 'decimal:2',
        'estimated_reduction_in_cost' => 'decimal:2',
        'estimated_revenue_increase' => 'decimal:2',
        'roi_assessment' => 'boolean',
        'risk_assessment' => 'boolean',
        'data_assessment' => 'boolean',
    ];
}
