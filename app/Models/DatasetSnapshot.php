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
        'supersedes_snapshot_id',
        'description',
        'time_range_start',
        'time_range_end',
        'row_count',
        'quality_checksums',
        'pii_element_count',
        'consent_coverage_at_creation',
        'file_count',
        'total_size',
        'size_unit',
        'file_format',
        'residency_zone',
        'storage_uri',
        'storage_tier',
        'compression',
        'encryption_status',
        'masking_method_applied',
        'created_by_system',
        'approved_by',
        'expiration_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'time_range_start' => 'datetime',
            'time_range_end' => 'datetime',
            'expiration_date' => 'date',
            'row_count' => 'integer',
            'pii_element_count' => 'integer',
            'file_count' => 'integer',
            'total_size' => 'integer',
            'consent_coverage_at_creation' => 'integer',
            'created_by_system' => 'boolean',
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
