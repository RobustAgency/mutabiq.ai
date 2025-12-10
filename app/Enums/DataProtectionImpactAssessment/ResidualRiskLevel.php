<?php

namespace App\Enums\DataProtectionImpactAssessment;

enum ResidualRiskLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
}
