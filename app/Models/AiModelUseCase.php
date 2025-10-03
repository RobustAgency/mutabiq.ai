<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiModelUseCase extends Model
{
    /** @use HasFactory<\Database\Factories\AiModelUseCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'business_objective',
        'status',
        'business_domain',
        'business_owner_email',
        'technical_owner_email',
        'regulatory_scope',
        'data_sensitivity',
        'go_live_date',
        'expected_roi',
        'implementation_cost',
        'reduction_in_time',
        'reduction_in_cost',
        'increase_in_revenue',
        'risk_avoidance',
        'fte_capacity_saved',
        'use_case_type',
        'value_driver',
        'risk_level',
        'overall_risk_score',
        'human_oversight_mode',
        'dpia',
        'aia',
        'data_availability_status',
        'data_readiness_level',
        'data_freshness',
    ];

    protected $casts = [
        'go_live_date' => 'date',
        'expected_roi' => 'float',
        'reduction_in_time' => 'float',
        'dpia' => 'boolean',
        'aia' => 'boolean',
    ];
}
