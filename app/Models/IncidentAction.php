<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncidentAction extends Model
{
    /** @use HasFactory<\Database\Factories\IncidentActionFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'ai_incident_id',
        'action_type',
        'execution_status',
        'individual_name',
        'depends_on',
        'approval_required',
        'estimated_duration',
        'actual_duration',
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

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the AI incident that owns the action.
     *
     * @return BelongsTo<AiIncident, $this>
     */
    public function aiIncident(): BelongsTo
    {
        return $this->belongsTo(AiIncident::class);
    }

    public function getDisplayIdAttribute(): string
    {
        return 'IA-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
