<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dataset extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'dataset_id',
        'name',
        'source_ids',
        'purpose',
        'schema_summary',
        'sensitivity',
        'contains_pii',
        'data_subject_categories',
        'controller_role',
        'lawful_basis',
        'lawful_basis_detail',
        'consent_required',
        'consent_coverage_pct',
        'consent_source_ref',
        'licensing_basis',
        'license_type',
        'privacy_notice_ref',
        'cross_border_transfer',
        'data_structure',
        'storage_format',
        'content_types',
        'retention_policy_ref',
        'dpia_ref',
        'aia_ref',
        'owner_team',
        'refresh_cadence',
        'quality_SLA',
        'catalog_asset_id',
        'catalog_uri',
    ];

    protected function casts(): array
    {
        return [
            'purpose' => 'array',
            'source_ids' => 'array',
            'data_subject_categories' => 'array',
            'content_types' => 'array',
            'consent_required' => 'boolean',
            'consent_coverage_pct' => 'integer',
        ];
    }

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
}
