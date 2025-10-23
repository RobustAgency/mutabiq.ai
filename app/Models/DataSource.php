<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    /** @use HasFactory<\Database\Factories\DataSourceFactory> */
    use HasFactory;

    protected $fillable = [
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
}
