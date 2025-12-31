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
        'source_reference',
        'ai_model_id',
        'dataset_id',
        'title',
        'capa_type',
        'priority',
        'root_cause',
        'actions',
        'owner_team',
        'assignee',
        'due_date',
        'status',
        'success_criteria',
        'linked_training',
        'estimated_cost',
        'effectiveness_review_date',
        'verification_result',
        'evidence_link',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'effectiveness_review_date' => 'date',
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
