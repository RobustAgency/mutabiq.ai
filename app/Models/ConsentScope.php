<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConsentScope extends Model
{
    /** @use HasFactory<\Database\Factories\ConsentScopeFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'dataset_id',
        'purpose',
        'subject_realm',
        'jurisdiction',
        'effective_from',
        'effective_to',
        'source_created_at',
    ];

    protected function casts(): array
    {
        return [
            'purpose' => 'array',
            'effective_from' => 'date',
            'effective_to' => 'date',
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

    public function getDisplayIdAttribute(): string
    {
        return 'CS-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
