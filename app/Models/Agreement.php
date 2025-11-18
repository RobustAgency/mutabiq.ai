<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agreement extends Model
{
    /** @use HasFactory<\Database\Factories\AgreementFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'vendor_id',
        'agreement_type',
        'status',
        'effective_from',
        'effective_to',
        'training_opt_out',
        'audit_rights',
        'transfer_mechanism',
        'sla_terms',
        'doc_ref',
    ];

    protected $casts = [
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'sla_terms' => 'array',
    ];

    protected $appends = [
        'display_id',
    ];

    /**
     * @return BelongsTo<Vendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function getDisplayIdAttribute(): string
    {
        return 'AG-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
