<?php

namespace App\Enums\KriIndicator;

enum Directionality: string
{
    case HIGHER_IS_RISKIER = 'higher_is_riskier';
    case LOWER_IS_RISKIER = 'lower_is_riskier';
}
