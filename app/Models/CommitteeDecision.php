<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommitteeDecision extends Model
{
    /** @use HasFactory<\Database\Factories\CommitteeDecisionFactory> */
    use HasFactory;

    protected $fillable = [
        'committee_meeting_id',
        'decision_type',
        'decision_scope',
        'ai_model_id',
        'use_case_id',
        'control_id',
        'related_ref',
        'rationale',
        'conditions',
        'expiry_date',
        'vote_method',
        'vote_result',
        'owner_team',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<CommitteeMeeting, $this>
     */
    public function committeeMeeting(): BelongsTo
    {
        return $this->belongsTo(CommitteeMeeting::class);
    }

    /**
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }

    /**
     * @return BelongsTo<UseCase, $this>
     */
    public function useCase(): BelongsTo
    {
        return $this->belongsTo(UseCase::class);
    }

    /**
     * @return BelongsTo<Control, $this>
     */
    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }
}
