<?php

namespace App\Enums\ConsentRecord;

enum SourceSystem: string
{
    case PORTAL = 'portal';
    case MOBILE_APP = 'mobile_app';
    case CRM = 'crm';
    case CALL_CENTER = 'call_center';
    case ADMIN = 'admin';
    case EMAIL = 'email';
    case WEBSITE = 'website';
}
