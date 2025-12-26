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
        'agreement_owner_id',
        'asset_types_covered',
        'renewal_type',
        'notice_period_days',
        'termination_for_convenience',
        'sub_processing_rights',
        'contract_value',
        'liability_cap',
        'insurance_requirements',
        'indemnification',
        'internal_reference_number',
        'vendor_contract_id',
        'dispute_resolution',
        'confidentiality_term',
        'parent_agreement',
        'governing_law',
        'replaces_agreement',
        'notes',
        'effective_from',
        'effective_to',
        'training_opt_out',
        'audit_rights',
        'transfer_mechanism',
        'sla_terms',
        'doc_ref',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'sla_terms' => 'array',
        'asset_types_covered' => 'array',
        'notice_period_days' => 'integer',
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
