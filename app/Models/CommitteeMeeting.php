<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommitteeMeeting extends Model
{
    /** @use HasFactory<\Database\Factories\CommitteeMeetingFactory> */
    use HasFactory;

    protected $fillable = [
        'ai_committee_id',
        'meeting_type',
        'scheduled_at',
        'duration_minutes',
        'agenda',
        'materials_link',
        'attendance_policy',
        'attendance_roster',
        'minutes_link',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'attendance_roster' => 'array',
        'duration_minutes' => 'integer',
    ];

    /**
     * @return BelongsTo<AiCommittee, $this>
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(AiCommittee::class);
    }
}
