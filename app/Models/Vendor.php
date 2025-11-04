<?php

namespace App\Models;

use App\Enums\Vendor\RiskTier;
use App\Enums\Vendor\VendorStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * @return BelongsTo<Stakeholder, $this>
     */
    public function stakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'stakeholder_id');
    }
}
