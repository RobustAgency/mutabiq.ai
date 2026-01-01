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
        'template',
        'language',
        'audience_type',
        'channel',
        'regulatory_basis',
        'notification_deadline',
        'notice_summary',
        'notice_link',
        'sent_at',
        'sent_by',
        'delivery_status',
        'response_summary',
        'follow_up_required',
        'follow_up_date',
        'follow_up_notes',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'notification_deadline' => 'datetime',
            'follow_up_date' => 'datetime',
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
