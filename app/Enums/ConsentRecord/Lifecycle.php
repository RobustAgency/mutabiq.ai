<?php

namespace App\Enums\ConsentRecord;

enum Lifecycle: string
{
    case OBTAINED = 'obtained';
    case ACTIVE = 'active';
    case EXPIRING = 'expiring';
    case EXPIRED = 'expired';
    case WITHDRAWN = 'withdrawn';
}
