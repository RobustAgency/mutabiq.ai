<?php

namespace App\Enums\DataElement;

enum CdeCategory: string
{
    case STRATEGIC = 'strategic';
    case OPERATIONAL = 'operational';
    case COMPLIANCE_REGULATORY = 'compliance_regulatory';
    case EXTERNAL_REPORTING = 'external_reporting';
    case FINANCIAL = 'financial';
    case RISK_MANAGEMENT = 'risk_management';
    case CUSTOMER_EXPERIENCE = 'customer_experience';
    case ANALYTICS_BI = 'analytics_bi';
}
