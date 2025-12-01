<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncidentNotification extends Model
{
    /** @use HasFactory<\Database\Factories\IncidentNotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'ai_incident_id',
        'audience_type',
        'channel',
        'notice_summary',
        'notice_link',
        'notified_at',
        'approved_by',
        'approval_ref',
        'follow_up_required',
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
            'follow_up_required' => 'boolean',
        ];
    }

    protected $appends = [
        'display_id',
    ];

    /**
     * Get the AI incident that owns the notification.
     *
     * @return BelongsTo<AiIncident, $this>
     */
    public function aiIncident(): BelongsTo
    {
        return $this->belongsTo(AiIncident::class);
    }

    public function getDisplayIdAttribute(): string
    {
        return 'IN-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
