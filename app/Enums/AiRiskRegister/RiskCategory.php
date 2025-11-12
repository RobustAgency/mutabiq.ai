<?php

namespace App\Enums\AiRiskRegister;

enum RiskCategory: string
{
    case SAFETY = 'safety';
    case PRIVACY = 'privacy';
    case BIAS_FAIRNESS = 'bias_fairness';
    case SECURITY = 'security';
    case ROBUSTNESS = 'robustness';
    case EXPLAINABILITY = 'explainability';
    case LEGAL_COMPLIANCE = 'legal_compliance';
    case ETHICS = 'ethics';
    case AVAILABILITY = 'availability';
    case RESILIENCE = 'resilience';
    case VENDOR = 'vendor';
    case COST = 'cost';
    case REPUTATION = 'reputation';
    case OTHER = 'other';
}
