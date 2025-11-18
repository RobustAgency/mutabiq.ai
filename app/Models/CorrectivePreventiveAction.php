<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CorrectivePreventiveAction extends Model
{
    /** @use HasFactory<\Database\Factories\CorrectivePreventiveActionFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'source_type',
        'source_id',
        'ai_model_id',
        'title',
        'capa_type',
        'priority',
        'owner_team',
        'assignee',
        'root_cause',
        'actions',
        'due_date',
        'status',
        'verification_result',
        'evidence_link',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    /**
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }

    public function getDisplayIdAttribute(): string
    {
        return 'CAPA-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
