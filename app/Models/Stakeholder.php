<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stakeholder extends Model
{
    /** @use HasFactory<\Database\Factories\StakeholderFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'type',
        'display_name',
        'first_name',
        'last_name',
        'org_unit',
        'email',
        'secondary_email',
        'phone',
        'mobile',
        'role_tags',
        'timezone',
        'classification',
        'country',
        'external_ref',
        'employee_id',
        'cost_center',
        'manager',
        'delegate',
        'status',
        'notes',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'role_tags' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $appends = [
        'display_id',
    ];

    public function getDisplayIdAttribute(): string
    {
        return 'SH-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
