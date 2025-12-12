<?php

namespace App\Enums\PrivacyIncident;

enum Status: string
{
    case DETECTED = 'detected';
    case UNDER_INVESTIGATION = 'under_investigation';
    case CONTAINED = 'contained';
    case NOTIFIED = 'notified';
    case REMEDIATION = 'remediation';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
}
