<?php

namespace App\Enums\AiIncident;

enum ImpactedDataType: string
{
    case PII = 'pii';
    case SENSITIVE_PERSONAL = 'sensitive_personal';
    case FINANCIAL = 'financial';
    case HEALTH = 'health';
    case IP_COPYRIGHT = 'ip_copyright';
    case NONE = 'none';
    case UNKNOWN = 'unknown';
}
