<?php

namespace App\Enums;

enum IncidentCategory: string
{
    case SAFETY = 'safety';
    case PRIVACY = 'privacy';
    case SECURITY = 'security';
    case BIAS_FAIRNESS = 'bias_fairness';
    case RELIABILITY = 'reliability';
    case AVAILABILITY = 'availability';
    case LEGAL_COMPLIANCE = 'legal_compliance';
    case VENDOR = 'vendor';
    case OTHER = 'other';
}
