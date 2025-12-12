<?php

namespace App\Enums\PrivacyIncident;

enum RiskLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case SEVERE = 'severe';
}
