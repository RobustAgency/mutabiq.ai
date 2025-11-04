<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Get the AI incident that owns the notification.
     * 
     * @return BelongsTo<AiIncident, $this>
     */
    public function aiIncident(): BelongsTo
    {
        return $this->belongsTo(AiIncident::class);
    }
}
