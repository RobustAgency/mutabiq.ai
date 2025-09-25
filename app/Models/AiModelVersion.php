<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiModelVersion extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ai_model_id',
        'version_number',
        'version_type',
        'description',
        'release_date',
        'release_notes',
        'architecture_type',
        'model_file_size_gb',
        'training_duration_hours',
        'complexity_level',
        'parameter_count',
        'input_modalities',
        'output_modalities',
        'deployment_status',
        'lifecycle_stage',
        'compliance_check_status',
        'validation_status',
        'deployment_environments',
        'rollback_available',
        'has_performance_data',
        'performance_baseline_established',
    ];

    protected $casts = [
        'input_modalities' => 'array',
        'output_modalities' => 'array',
        'deployment_environments' => 'array',
        'rollback_available' => 'boolean',
        'has_performance_data' => 'boolean',
        'performance_baseline_established' => 'boolean',
    ];

}
