<?php

namespace App\Enums\PrivacyIncident;

enum NotificationRequired: string
{
    case NONE = 'none';
    case AUTHORITY = 'authority';
    case SUBJECTS = 'subjects';
    case BOTH = 'both';
}
