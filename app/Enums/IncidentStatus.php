<?php

namespace App\Enums;

enum IncidentStatus: string
{
    case OPEN = 'open';
    case CONTAINED = 'contained';
    case MONITORING = 'monitoring';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
}
