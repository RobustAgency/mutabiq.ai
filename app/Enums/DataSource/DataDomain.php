<?php

namespace App\Enums\DataSource;

enum DataDomain: string
{
    case CUSTOMER = 'customer';
    case FINANCE = 'finance';
    case OPERATIONS = 'operations';
    case HUMAN_RESOURCES = 'human_resources';
    case MARKETING = 'marketing';
    case PRODUCT = 'product';
    case SALES = 'sales';
    case LEGAL = 'legal';
    case IT_TECHNOLOGY = 'it_technology';
    case SUPPLY_CHAIN = 'supply_chain';
}
