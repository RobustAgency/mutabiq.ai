<?php

namespace App\Enums\PrivacyIncident;

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case NOT_REQUIRED = 'not_required';
    case IN_PROGRESS = 'in_progress';
    case AUTHORITY_NOTIFIED = 'authority_notified';
    case SUBJECTS_NOTIFIED = 'subjects_notified';
    case COMPLETED = 'completed';
}
