<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stakeholder extends Model
{
    /** @use HasFactory<\Database\Factories\StakeholderFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'display_name',
        'legal_name',
        'org_unit',
        'email',
        'phone',
        'vendor_id',
        'role_tags',
        'timezone',
        'classification',
        'country',
        'external_ref',
        'active',
    ];

    protected $casts = [
        'role_tags' => 'array',
        'active' => 'boolean',
    ];
}
