<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncidentAlert extends Model
{
    /** @use HasFactory<\Database\Factories\IncidentAlertFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'ai_incident_id',
        'source_type',
        'data_source_id',
        'source_ref',
        'alert_sensitivity',
        'context',
        'first_seen_at',
        'last_seen_at',
        'auto_promote_incident',
        'evidence_link',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'auto_promote_incident' => 'boolean',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the AI incident that owns the alert.
     *
     * @return BelongsTo<AiIncident, $this>
     */
    public function aiIncident(): BelongsTo
    {
        return $this->belongsTo(AiIncident::class);
    }

    public function getDisplayIdAttribute(): string
    {
        return 'IAL-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
