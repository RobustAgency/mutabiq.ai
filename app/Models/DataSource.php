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
        'system_type',
        'owner_team',
        'data_domains',
        'access_method',
        'residency',
        'classification',
        'hosting_model',
        'service_model',
        'cloud_provider',
        'primary_region',
        'secondary_region',
        'network_ref',
        'retention_policy_ref',
        'catalog_uri',
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
