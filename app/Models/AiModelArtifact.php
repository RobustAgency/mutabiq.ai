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

    /**
     * Get the user who created this artifact.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'email');
    }

    /**
     * Get the human-readable size.
     */
    public function getFormattedSizeAttribute(): ?string
    {
        if (!$this->size_bytes) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->size_bytes;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
