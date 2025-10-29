<?php

namespace App\Enums\AiIncident;

enum IncidentSeverity: string
{
    case SEV1_CRITICAL = 'sev1_critical';
    case SEV2_HIGH = 'sev2_high';
    case SEV3_MEDIUM = 'sev3_medium';
    case SEV4_LOW = 'sev4_low';
    case NEAR_MISS = 'near_miss';
}
