<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentAlert extends Model
{
    /** @use HasFactory<\Database\Factories\IncidentAlertFactory> */
    use HasFactory;

    protected $fillable = [
        'ai_incident_id',
        'source_type',
        'source_ref',
        'rule_version',
        'context',
        'first_seen_at',
        'last_seen_at',
        'evidence_link',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Get the AI incident that owns the alert.
     * 
     * @return BelongsTo<AiIncident, $this>
     */
    public function aiIncident(): BelongsTo
    {
        return $this->belongsTo(AiIncident::class);
    }
}
