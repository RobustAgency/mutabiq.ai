<?php

namespace App\Enums;

enum StrategicImportance: string
{
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';
    case CRITICAL = 'critical';
}
