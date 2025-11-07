<?php

namespace App\Models;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * @return BelongsTo<Dataset, $this>
     */
    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }
}
