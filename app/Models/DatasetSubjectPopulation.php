<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetSubjectPopulation extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetSubjectPopulationFactory> */
    use HasFactory;

    protected $fillable = [
        'dataset_id',
        'snapshot_id',
        'subject_realm',
        'jurisdiction',
        'subjects_total',
        'as_of',
    ];

    protected $casts = [
        'subjects_total' => 'integer',
        'as_of' => 'datetime',
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
}
