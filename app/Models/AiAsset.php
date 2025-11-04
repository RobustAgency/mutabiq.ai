<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAsset extends Model
{
    /** @use HasFactory<\Database\Factories\AiAssetFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'vendor_id',
        'vendor_effective_from',
        'vendor_effective_to',
        'vendor_agreement_id',
        'vendor_assessment_id',
    ];

    protected $casts = [
        'vendor_effective_from' => 'datetime',
        'vendor_effective_to' => 'datetime',
    ];

    /**
     * Get the vendor associated with the AI asset.
     * 
     * @return BelongsTo<Vendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get the vendor agreement for the AI asset.
     * 
     * @return BelongsTo<Agreement, $this>
     */
    public function vendorAgreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class, 'vendor_agreement_id');
    }
}
