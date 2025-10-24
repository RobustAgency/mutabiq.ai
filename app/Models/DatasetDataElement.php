<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetDataElement extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetDataElementFactory> */
    use HasFactory;

    protected $table = 'dataset_element';

    protected $fillable = [
        'dataset_id',
        'data_element_id',
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
    ];

    /**
     * @return BelongsTo<Dataset, $this>
     */
    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    /**
     * @return BelongsTo<DataElement, $this>
     */
    public function dataElement(): BelongsTo
    {
        return $this->belongsTo(DataElement::class);
    }
}
