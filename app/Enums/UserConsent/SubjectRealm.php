<?php

namespace App\Enums\UserConsent;

enum SubjectRealm: string
{
    case CUSTOMER = 'customer';
    case PROSPECT = 'prospect';
    case EMPLOYEE = 'employee';
    case VENDOR = 'vendor';
    case OTHER = 'other';
}
