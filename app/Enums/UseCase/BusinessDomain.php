<?php

namespace App\Enums\UseCase;

enum BusinessDomain: string
{
    case CUSTOMER_SERVICE = 'customer_service';
    case FRAUD_DETECTION = 'fraud_detection';
    case MARKETING = 'marketing';
    case OPERATIONS = 'operations';
    case RISK_MANAGEMENT = 'risk_management';
    case HR = 'hr';
    case FINANCE = 'finance';
    case LEGAL = 'legal';
    case PRODUCT_DEVELOPMENT = 'product_development';
    case SUPPLY_CHAIN = 'supply_chain';
}
