<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ArtifactAccessLog\AccessAction;
use App\Enums\ArtifactAccessLog\AccessContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArtifactAccessLog extends Model
{
    /** @use HasFactory<\Database\Factories\ArtifactAccessLogFactory> */
    use HasFactory;

    protected $fillable = [
        'artifact_id',
        'accessor_stakeholder_id',
        'action',
        'context',
        'ts',
        'ip_or_agent',
        'request_id',
        'reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'action' => AccessAction::class,
        'context' => AccessContext::class,
        'ts' => 'datetime',
    ];

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the artifact that was accessed
     *
     * @return BelongsTo<AiModelArtifact, $this>
     */
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(AiModelArtifact::class, 'artifact_id');
    }

    /**
     * Get the stakeholder who accessed the artifact
     *
     * @return BelongsTo<Stakeholder, $this>
     */
    public function accessorStakeholder(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'accessor_stakeholder_id');
    }

    public function getDisplayIdAttribute(): string
    {
        return 'AAL-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
