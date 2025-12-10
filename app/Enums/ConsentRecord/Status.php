<?php

namespace App\Enums\ConsentRecord;

enum Status: string
{
    case GRANTED = 'granted';
    case DENIED = 'denied';
    case WITHDRAWN = 'withdrawn';
    case EXPIRED = 'expired';
}
