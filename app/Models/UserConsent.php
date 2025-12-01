<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserConsent extends Model
{
    /** @use HasFactory<\Database\Factories\UserConsentFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'subject_key',
        'subject_realm',
        'jurisdiction',
        'consent_purpose',
        'consent_status',
        'legal_basis',
        'source_system',
        'evidence_ref',
        'effective_from',
        'effective_to',
        'scope',
    ];

    protected function casts(): array
    {
        return [
            'consent_purpose' => 'array',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    public function getDisplayIdAttribute(): string
    {
        return 'UC-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
