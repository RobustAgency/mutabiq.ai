<?php

namespace App\Enums\AiIncident;

enum IncidentStatus: string
{
    case OPEN = 'open';
    case INVESTIGATING = 'investigating';
    case CONTAINED = 'contained';
    case MITIGATED = 'mitigated';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    case REOPENED = 'reopened';
}
