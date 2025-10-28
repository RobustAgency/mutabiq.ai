<?php

namespace App\Enums\DataElement;

enum CdeCategory: string
{
    case STRATEGIC = 'Strategic';
    case COMPLIANCE = 'Compliance';
    case EXTERNAL_REPORTING = 'External Reporting';
    case OPERATIONAL = 'Operational';
    case FINANCIAL = 'Financial';
    case RISK = 'Risk';
    case CUSTOMER_EXPERIENCE = 'Customer Experience';
}
