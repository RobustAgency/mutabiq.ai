<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    /** @use HasFactory<\Database\Factories\VendorFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'vendor_name',
        'legal_name',
        'hq_country',
        'risk_tier',
        'status',
        'stakeholder_id',
        'primary_contacts',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'primary_contacts' => 'array',
        'metadata' => 'array',
    ];

    protected $appends = [
        'display_id',
    ];

    /**
     * @return BelongsTo<Stakeholder, $this>
     */
    public function stakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'stakeholder_id');
    }

    public function getDisplayIdAttribute(): string
    {
        return 'VND-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
