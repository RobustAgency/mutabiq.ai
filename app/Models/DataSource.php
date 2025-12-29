<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataSource extends Model
{
    /** @use HasFactory<\Database\Factories\DataSourceFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'system_type',
        'owner_team',
        'data_domains',
        'residency',
        'criticality_level',
        'hosting_model',
        'technical_owner',
        'business_owner',
        'last_review_date',
        'next_review_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_domains' => 'array',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    public function getDisplayIdAttribute(): string
    {
        return 'DS-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
