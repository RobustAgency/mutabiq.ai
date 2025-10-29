<?php

namespace App\Enums\IncidentNotification;

enum AudienceType: string
{
    case INTERNAL_EXEC = 'internal_exec';
    case INTERNAL_STAFF = 'internal_staff';
    case CUSTOMERS = 'customers';
    case REGULATOR = 'regulator';
    case VENDOR = 'vendor';
    case MEDIA = 'media';
    case OTHER = 'other';
}
