<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiModelCard extends Model
{
    /** @use HasFactory<\Database\Factories\AiModelCardFactory> */
    use HasFactory;

    protected $fillable = [
        'ai_model_id',
        'ai_model_version_id',
        'title',
        'version',
        'creator_role',
        'access_level',
        'owner_email',
        'format',
        'status',
        'workflow_stage',
        'technical_review_status',
        'ethics_review_status',
        'compliance_review_status',
        'publication_status',
        'completeness_score',
        'organizational_context',
        'intended_use',
        'training_data_overview',
        'bias_evaluation_methods',
        'model_limitations',
        'ethical_considerations',
        'risk_summary',
        'performance_summary',
        'latest_performance_date',
        'publication_date',
        'last_review_date',
        'next_review_date',
    ];
}
