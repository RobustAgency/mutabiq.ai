<?php

namespace App\Enums\UseCase;

enum BusinessDomain: string
{
    case OPERATIONS = 'operations';
    case FINANCE = 'finance';
    case RISK = 'risk';
    case COMPLIANCE = 'compliance';
    case CUSTOMER_SERVICE = 'customer_service';
    case HR = 'hr';
    case MARKETING = 'marketing';
    case SALES = 'sales';
    case IT = 'it';
    case PROCUREMENT = 'procurement';
    case SUPPLY_CHAIN = 'supply_chain';
    case LEGAL = 'legal';
    case STRATEGY = 'strategy';
    case OTHER = 'other';

}
