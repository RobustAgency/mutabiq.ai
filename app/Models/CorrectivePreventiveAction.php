<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }
}
