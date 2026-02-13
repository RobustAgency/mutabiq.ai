<?php

namespace App\Observers;

use App\Models\IncidentNotification;

class IncidentNotificationObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
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
    }

    public function created(IncidentNotification $notification): void
    {
        $this->logCreate($notification);
    }

    public function updating(IncidentNotification $notification): void
    {
        $this->logUpdate($notification, $notification->getOriginal());
    }

    public function deleted(IncidentNotification $notification): void
    {
        $this->logDelete($notification);
    }
}
