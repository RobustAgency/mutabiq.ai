<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentCoverage extends Model
{
    /** @use HasFactory<\Database\Factories\ConsentCoverageFactory> */
    use HasFactory;

    protected $fillable = [
        'dataset_id',
        'snapshot_id',
        'purpose',
        'jurisdiction',
        'as_of',
        'subjects_total',
        'subjects_with_valid_consent',
        'coverage_pct',
        'evidence_ref'
    ];

    protected function casts(): array
    {
        return [
            'as_of' => 'datetime',
            'subjects_total' => 'integer',
            'subjects_with_valid_consent' => 'integer',
            'coverage_pct' => 'decimal:2',
        ];
    }

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
}
