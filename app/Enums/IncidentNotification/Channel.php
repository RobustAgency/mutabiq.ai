<?php

namespace App\Enums\IncidentNotification;

enum Channel: string
{
    case EMAIL = 'email';
    case PORTAL = 'portal';
    case STATUS_PAGE = 'status_page';
    case PHONE = 'phone';
    case MEETING = 'meeting';
    case LEGAL_LETTER = 'legal_letter';
    case OTHER = 'other';
}
