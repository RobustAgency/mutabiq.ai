<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConsentCoverage extends Model
{
    /** @use HasFactory<\Database\Factories\ConsentCoverageFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'dataset_id',
        'snapshot_id',
        'purpose',
        'jurisdiction',
        'as_of',
        'subjects_total',
        'subjects_with_valid_consent',
        'coverage_pct',
        'evidence_ref',
        'source_created_at',
    ];

    protected function casts(): array
    {
        return [
            'purpose' => 'array',
            'as_of' => 'datetime',
            'subjects_total' => 'integer',
            'subjects_with_valid_consent' => 'integer',
            'coverage_pct' => 'decimal:2',
            'source_created_at' => 'datetime',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    /**
     * @return BelongsTo<Dataset, $this>
     */
    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    /**
     * @return BelongsTo<DatasetSnapshot, $this>
     */
    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(DatasetSnapshot::class, 'snapshot_id');
    }

    public function getDisplayIdAttribute(): string
    {
        return 'CC-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
