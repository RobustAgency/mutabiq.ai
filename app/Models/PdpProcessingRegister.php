<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PdpProcessingRegister extends Model
{
    /** @use HasFactory<\Database\Factories\PdpProcessingRegisterFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'purpose',
        'controller_role',
        'data_subject_categories',
        'personal_data_categories',
        'lawful_basis',
        'lawful_basis_detail',
        'retention_policy_ref',
        'recipients',
        'international_transfer_ref',
        'dpia_required_flag',
        'security_measures_ref',
        'owner_team',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected $casts = [
        'data_subject_categories' => 'array',
        'personal_data_categories' => 'array',
        'recipients' => 'array',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
    ];

    protected $appends = [
        'display_id',
    ];

    public function getDisplayIdAttribute(): string
    {
        return 'PDP-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
