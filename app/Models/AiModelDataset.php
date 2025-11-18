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
        'access_path',
        'transform_pack_link',
        'license_check_ref',
        'privacy_check_ref',
        'eligibility_status',
        'notes',
        'source_created_at',
    ];

    protected function casts(): array
    {
        return [
            'source_created_at' => 'datetime',
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
