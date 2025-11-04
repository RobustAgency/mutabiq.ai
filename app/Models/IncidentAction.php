<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentAction extends Model
{
    /** @use HasFactory<\Database\Factories\IncidentActionFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'ai_incident_id',
        'action_type',
        'description',
        'performed_by',
        'started_at',
        'completed_at',
        'validation_result',
        'validation_notes',
        'linked_release_id',
        'evidence_link',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the AI incident that owns the action.
     * 
     * @return BelongsTo<AiIncident, $this>
     */
    public function aiIncident(): BelongsTo
    {
        return $this->belongsTo(AiIncident::class);
    }
}
