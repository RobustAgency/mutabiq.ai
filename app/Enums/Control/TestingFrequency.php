<?php

namespace App\Enums\Control;

enum TestingFrequency: string
{
    case RELEASE = 'release';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case ANNUALLY = 'annually';
    case EVENT_DRIVEN = 'event-driven';
}
