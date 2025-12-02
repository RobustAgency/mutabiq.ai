<?php

namespace App\Enums\KriIndicator;

enum Frequency: string
{
    case QUARTER_HOURLY = 'quarter_hourly';
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
}
