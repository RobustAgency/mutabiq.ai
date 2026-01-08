<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommitteeAction extends Model
{
    /** @use HasFactory<\Database\Factories\CommitteeActionFactory> */
    use HasFactory;

    protected $fillable = [
        'committee_decision_id',
        'title',
        'action_type',
        'assignee_id',
        'due_date',
        'status',
        'verification_result',
        'evidence_link',
        'notes',
        'closed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the committee decision that owns the action.
     *
     * @return BelongsTo<CommitteeDecision, $this>
     */
    public function committeeDecision(): BelongsTo
    {
        return $this->belongsTo(CommitteeDecision::class);
    }

    /**
     * Get the stakeholder assigned to the action.
     *
     * @return BelongsTo<Stakeholder, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Stakeholder::class, 'assignee_id');
    }
}
