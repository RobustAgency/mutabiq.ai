<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiModelDataset extends Model
{
    /** @use HasFactory<\Database\Factories\AiModelDatasetFactory> */
    use HasFactory;

    protected $table = 'ai_model_dataset';

    protected $fillable = [
        'organization_id',
        'ai_model_id',
        'ai_model_version_id',
        'dataset_id',
        'dataset_snapshot_id',
        'role',
        'rows_used',
        'training_start_date',
        'training_end_date',
        'training_duration',
        'compute_resources',
        'cost',
        'consent_check_status',
        'cross_border_check',
        'special_category_check',
        'bias_mitigation_applied',
        'created_by_system',
        'linkage_status',
        'business_justification',
    ];

    protected function casts(): array
    {
        return [
            'rows_used' => 'integer',
            'training_start_date' => 'date',
            'training_end_date' => 'date',
            'cost' => 'decimal:2',
            'bias_mitigation_applied' => 'boolean',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the organization that owns this AI model dataset.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the AI model associated with this dataset.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }

    /**
     * Get the AI model version associated with this dataset.
     *
     * @return BelongsTo<AiModelVersion, $this>
     */
    public function aiModelVersion(): BelongsTo
    {
        return $this->belongsTo(AiModelVersion::class);
    }

    /**
     * Get the dataset associated with this AI model dataset.
     *
     * @return BelongsTo<Dataset, $this>
     */
    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function getDisplayIdAttribute(): string
    {
        return 'AMD-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
