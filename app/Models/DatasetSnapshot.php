<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DatasetSnapshot extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'dataset_id',
        'version_tag',
        'time_range_start',
        'time_range_end',
        'row_count',
        'quality_checksums',
        'pii_element_count',
        'special_category_element_count',
        'masking_anonymization_method',
        'privacy_transform_evidence_ref',
        'residency_zone',
        'storage_uri',
        'source_created_at',
    ];

    protected function casts(): array
    {
        return [
            'time_range_start' => 'datetime',
            'time_range_end' => 'datetime',
            'row_count' => 'integer',
            'pii_element_count' => 'integer',
            'special_category_element_count' => 'integer',
            'source_created_at' => 'datetime',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the dataset that owns the snapshot.
     *
     * @return BelongsTo<Dataset, $this>
     */
    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function getDisplayIdAttribute(): string
    {
        return 'DSN-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
