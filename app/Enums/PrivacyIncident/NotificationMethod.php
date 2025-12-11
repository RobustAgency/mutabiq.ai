<?php

namespace App\Enums\PrivacyIncident;

enum NotificationMethod: string
{
    case EMAIL = 'email';
    case LETTER = 'letter';
    case PHONE = 'phone';
    case SMS = 'SMS';
    case WEBSITE = 'website';
    case MEDIA_ANNOUNCEMENT = 'media_announcement';
}
