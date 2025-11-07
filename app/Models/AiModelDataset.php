<?php

namespace App\Models;

use App\Enums\AiModelDataset\EligibilityStatus;
use App\Enums\AiModelDataset\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModelDataset extends Model
{
    /** @use HasFactory<\Database\Factories\AiModelDatasetFactory> */
    use HasFactory;

    protected $table = 'ai_model_dataset';

    protected $fillable = [
        'organization_id',
        'ai_model_id',
        'ai_model_version_id',
        'dataset_id',
        'dataset_snapshot_id',
        'role',
        'access_path',
        'transform_pack_link',
        'license_check_ref',
        'privacy_check_ref',
        'eligibility_status',
        'notes',
        'source_created_at',
    ];

    protected function casts(): array
    {
        return [
            'source_created_at' => 'datetime',
        ];
    }
}
