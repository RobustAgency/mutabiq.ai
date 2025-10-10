<?php

namespace App\Enums;

enum CardFormat: string
{
    case STANDARD = 'standard';
    case REGULATORY = 'regulatory';
    case INDUSTRY_SPECIFIC = 'industry_specific';
    case CUSTOM = 'custom';
}
