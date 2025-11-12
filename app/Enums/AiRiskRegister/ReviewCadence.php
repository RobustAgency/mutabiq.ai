<?php

namespace App\Enums\AiRiskRegister;

enum ReviewCadence: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case SEMI_ANNUAL = 'semi_annual';
    case ANNUAL = 'annual';
}
