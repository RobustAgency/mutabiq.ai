<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncidentRootCauseAnalysis extends Model
{
    /** @use HasFactory<\Database\Factories\IncidentRootCauseAnalysisFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'ai_incident_id',
        'rca_method',
        'immediate_cause',
        'latent_causes',
        'contributing_factors',
        'impact_assessment',
        'fixes_implemented',
        'lessons_learned',
        'recommendations',
        'approved_by',
        'approved_at',
        'report_link',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the AI incident that owns the root cause analysis.
     *
     * @return BelongsTo<AiIncident, $this>
     */
    public function aiIncident(): BelongsTo
    {
        return $this->belongsTo(AiIncident::class);
    }

    public function getDisplayIdAttribute(): string
    {
        return 'RCA-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
