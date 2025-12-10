<?php

namespace App\Enums\DataProtectionImpactAssessment;

enum RiskLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';
}
