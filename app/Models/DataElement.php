<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'data_type',
        'format',
        'sensitivity',
        'pii_flag',
        'personal_data_category',
        'special_category_flag',
        'cde_flag',
        'cde_category',
        'owner_team',
        'quality_rules_ref',
        'catalog_column_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'display_id',
    ];

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
