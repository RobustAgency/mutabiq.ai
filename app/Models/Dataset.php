<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dataset extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'dataset_id',
        'name',
        'description',
        'purpose',
        'owner_team',
        'data_steward',
        'source_ids',
        'status',
        'estimated_row_count',
        'estimated_size',
        'size_unit',
        'retention_period',
        'primary_languages',
        'contains_personal_data',
        'sensitivity',
        'cross_border_transfer',
        'license_type',
    ];

    protected function casts(): array
    {
        return [
            'source_ids' => 'array',
            'primary_languages' => 'array',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    /**
     * @return BelongsToMany<DataElement, $this>
     */
    public function dataElements(): BelongsToMany
    {
        return $this->belongsToMany(DataElement::class, 'dataset_element')
            ->withPivot([
                'column_name',
                'nullable',
                'sensitivity_override',
                'pii_override',
                'transform_applied',
                'quality_rules_applied',
                'cde_in_dataset',
                'cde_category_in_dataset',
                'lineage_source_column',
                'deprecated',
            ])
            ->withTimestamps();
    }

    /**
     * Get the snapshots for the dataset.
     *
     * @return HasMany<DatasetSnapshot, $this>
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(DatasetSnapshot::class);
    }

    public function getDisplayIdAttribute(): string
    {
        return 'DS-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
