<?php

namespace App\Enums\AiIncident;

enum AffectedBusinessUnit: string
{
    case FINANCE = 'finance';
    case HUMAN_RESOURCES = 'human_resources';
    case MARKETING = 'marketing';
    case SALES = 'sales';
    case OPERATIONS = 'operations';
    case CUSTOMER_SERVICE = 'customer_service';
    case IT_TECHNOLOGY = 'it_technology';
    case LEGAL = 'legal';
    case RESEARCH_DEVELOPMENT = 'research_development';
    case SUPPLY_CHAIN = 'supply_chain';
    case EXECUTIVE_OFFICE = 'executive_office';
    case ALL = 'all';
}
