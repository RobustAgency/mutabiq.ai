<?php

namespace App\Enums\UserConsent;

enum ConsentStatus: string
{
    case GRANTED = 'granted';
    case DENIED = 'denied';
    case WITHDRAWN = 'withdrawn';
    case EXPIRED = 'expired';
    case NOT_OBTAINED = 'not_obtained';
}
