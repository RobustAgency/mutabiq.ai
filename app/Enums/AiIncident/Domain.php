<?php

namespace App\Enums\AiIncident;

enum Domain: string
{
    case AI_GOVERNANCE = 'ai_governance';
    case DATA_PRIVACY = 'data_privacy';
    case DATA_GOVERNANCE = 'data_governance';
    case INFORMATION_SECURITY = 'information_security';
    case MULTIPLE_DOMAINS = 'multiple_domains';
}
