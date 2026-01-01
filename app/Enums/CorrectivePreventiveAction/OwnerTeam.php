<?php

namespace App\Enums\CorrectivePreventiveAction;

enum OwnerTeam: string
{
    case AI_GOVERNANCE = 'ai_governance';
    case DATA_PRIVACY_OFFICE = 'data_privacy_office';
    case DATA_GOVERNANCE = 'data_governance';
    case ML_ENGINEERING = 'ml_engineering';
    case DATA_ENGINEERING = 'data_engineering';
    case INFORMATION_SECURITY = 'information_security';
    case LEGAL = 'legal';
    case COMPLIANCE = 'compliance';
    case EXECUTIVE_LEADERSHIP = 'executive_leadership';
    case PRODUCT = 'product';
    case CUSTOMER_SUCCESS = 'customer_success';
}
