<?php

namespace App\Enums\IncidentAlert;

enum AlertSeverity: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';
}
