<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    protected $appends = [
        'display_id',
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

    public function getDisplayIdAttribute(): string
    {
        return 'AMA-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
