<?php

namespace App\Enums\CorrectivePreventiveAction;

enum SourceType: string
{
    case INCIDENT = 'incident';
    case RCA = 'rca';
    case AUDIT_FINDING = 'audit finding';
    case RISK_ASSESSMENT = 'risk assessment';
    case CUSTOMER_COMPLAINT = 'customer complaint';
    case REGULATORY_REQUIREMENT = 'regulatory requirement';
}
