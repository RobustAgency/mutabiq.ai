<?php

namespace App\Models;

use App\Enums\FrameworkCategory;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Framework extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\FrameworkFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'type',
        'geography',
        'category',
        'version',
        'release_date',
        'is_published',
        'description',
        'authority_publisher',
        'binding_level',
        'sector_applicability',
        'risk_class_coverage',
        'certification_attestation',
        'assessment_mode',
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_published' => 'boolean',
        'category' => FrameworkCategory::class,
    ];
}
