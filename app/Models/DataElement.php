<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DataElement extends Model
{
    /** @use HasFactory<\Database\Factories\DataElementFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'business_definition',
        'data_steward',
        'status',
        'data_source_id',
        'database_name',
        'schema_name',
        'table_name',
        'column_name',
        'used_in_datasets',
        'is_nullable',
        'is_unique',
        'default_value',
        'validation_rule',
        'sample_values',
        'data_type',
        'format',
        'sensitivity',
        'contains_personal_data',
        'personal_data_type',
        'contains_sensitive_data',
        'default_masking_method',
        'cde_flag',
        'cde_categories',
    ];

    protected $casts = [
        'used_in_datasets' => 'array',
        'cde_categories' => 'array',
        'is_nullable' => 'boolean',
        'is_unique' => 'boolean',
        'contains_personal_data' => 'boolean',
        'contains_sensitive_data' => 'boolean',
    ];

    protected $appends = [
        'display_id',
    ];

    /**
     * @return BelongsTo<DataSource, $this>
     */
    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class);
    }

    /**
     * @return BelongsToMany<Dataset, $this>
     */
    public function datasets(): BelongsToMany
    {
        return $this->belongsToMany(Dataset::class, 'dataset_element', 'data_element_id', 'dataset_id')
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
            ]);
    }

    public function getDisplayIdAttribute(): string
    {
        return 'DE-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
