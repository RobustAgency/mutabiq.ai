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
        'dataset_id',
        'purpose',
        'subject_realm',
        'jurisdiction',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'purpose' => ConsentPurpose::class,
            'subject_realm' => SubjectRealm::class,
            'jurisdiction' => Jurisdiction::class,
            'effective_from' => 'date',
            'effective_to' => 'date',
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
