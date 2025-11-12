<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModelVersion extends Model
{
    /** @use HasFactory<\Database\Factories\AiModelVersionFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'ai_model_id',
        'version_number',
        'version_type',
        'version_role',
        'version_source',
        'our_involvement',
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
        'deployment_environments',
        'has_performance_data',
        'customizations_applied',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'input_modalities' => 'array',
        'output_modalities' => 'array',
        'deployment_environments' => 'array',
        'rollback_available' => 'boolean',
        'has_performance_data' => 'boolean',
        'customizations_applied' => 'array',
    ];

    /**
     * Get the AI model that this version belongs to.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }
}
