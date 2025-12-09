<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentRecord extends Model
{
    protected $fillable = [
        'consent_code',
        'subject_key',
        'subject_realm',
        'subject_age_group',
        'purpose',
        'record_of_processing_activity_id',
        'status',
        'lifecycle_stage',
        'consent_version',
        'consent_text',
        'consent_method',
        'effective_from',
        'effective_to',
        'obtained_date',
        'withdrawal_date',
        'last_refreshed_date',
        'source_system',
        'evidence_uri',
        'ip_address',
        'user_agent',
        'language',
        'jurisdiction',
        'data_categories',
        'can_withdraw',
        'withdrawal_method',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'obtained_date' => 'date',
        'withdrawal_date' => 'date',
        'last_refreshed_date' => 'date',
        'can_withdraw' => 'boolean',
        'data_categories' => 'array',
    ];
}
