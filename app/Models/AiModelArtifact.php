<?php

namespace App\Models;

use App\Enums\ArtifactType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModelArtifact extends Model
{
    /** @use HasFactory<\Database\Factories\AiModelArtifactFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'ai_model_version_id',
        'name',
        'artifact_type',
        'uri',
        'checksum',
        'size_bytes',
        'created_by',
        'notes',
    ];

    /**
     * Get the AI model version that owns this artifact.
     *
     * @return BelongsTo<AiModelVersion, $this>
     */
    public function aiModelVersion(): BelongsTo
    {
        return $this->belongsTo(AiModelVersion::class);
    }
}
