<?php

namespace App\Enums\DataSource;

enum DataResidency: string
{
    case AE = 'AE'; // United Arab Emirates
    case EU = 'EU'; // European Union
    case KSA = 'KSA'; // Kingdom of Saudi Arabia
    case US = 'US'; // United States
    case UK = 'UK'; // United Kingdom
    case QA = 'QA'; // Qatar
    case JO = 'JO'; // Jordan
    case MA = 'MA'; // Morocco
    case BH = 'BH'; // Bahrain
    case OTHER = 'Other';
}
